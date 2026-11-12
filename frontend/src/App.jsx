import { useMemo, useState } from "react";
import "./App.css";

const vehicles = [
  { id: 1, name: "Toyota Avanza", plate: "DK 1234 XX", type: "MPV", seats: 7, transmission: "Manual", image: "https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=1000&q=85" },
  { id: 2, name: "Toyota Innova", plate: "DK 5678 XX", type: "MPV", seats: 7, transmission: "Automatic", image: "https://images.unsplash.com/photo-1617469767053-d3b523a0b982?auto=format&fit=crop&w=1000&q=85" },
  { id: 3, name: "Mitsubishi Xpander", plate: "DK 9012 XX", type: "MPV", seats: 7, transmission: "Automatic", image: "https://images.unsplash.com/photo-1603386329225-868f9b1ee6c9?auto=format&fit=crop&w=1000&q=85" },
];

export default function App() {
  const [page, setPage] = useState("dashboard");
  const [selectedVehicle, setSelectedVehicle] = useState(null);
  const [search, setSearch] = useState("");
  const [booking, setBooking] = useState({ full_name: "", employee_number: "", department: "", phone_number: "", destination: "", purpose: "", passenger_count: "", driver_name: "", start_datetime: "", end_datetime: "", });
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  const [history, setHistory] = useState([]);

  const filteredVehicles = useMemo(() => vehicles.filter((vehicle) =>
    `${vehicle.name} ${vehicle.plate}`.toLowerCase().includes(search.toLowerCase())
  ), [search]);

  function openBooking(vehicle) {
    setSelectedVehicle(vehicle);
    setPage("booking");
  }

  async function submitBooking(event) {
    event.preventDefault();

    if (!selectedVehicle) return;

    setSubmitting(true);
    setErrorMessage("");

    try {
      const response = await fetch(
        "http://127.0.0.1:8000/api/bookings",
        {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
          },
          body: JSON.stringify({
            ...booking,
            vehicle_id: selectedVehicle.id,
            passenger_count: Number(booking.passenger_count),
          }),
        }
      );

      const result = await response.json();

      if (!response.ok) {
        const validationErrors = result.errors
          ? Object.values(result.errors).flat().join("\n")
          : result.message;

        throw new Error(validationErrors || "Pengajuan booking gagal.");
      }

      const newBooking = {
        id: result.data.id,
        bookingCode: result.data.booking_code,
        name: booking.full_name,
        vehicle: selectedVehicle,
        startDate: booking.start_datetime,
        endDate: booking.end_datetime,
        downloadUrl: result.download_url,
      };

      setHistory((items) => [newBooking, ...items]);

      // Unduh PDF Model C dari Laravel.
      const link = document.createElement("a");
      link.href = result.download_url;
      link.click();

      setPage("history");

      setBooking({
        full_name: "",
        employee_number: "",
        department: "",
        phone_number: "",
        destination: "",
        purpose: "",
        passenger_count: 1,
        driver_name: "",
        start_datetime: "",
        end_datetime: "",
      });

    } catch (error) {
      setErrorMessage(error.message);
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <div className="app-shell">
      <aside className="sidebar">
        <div className="brand"><span className="brand-mark">C</span><span>car<span className="brand-light">pool</span></span></div>
        <p className="nav-caption">MENU UTAMA</p>
        <button className={`nav-link ${page === "dashboard" ? "active" : ""}`} onClick={() => setPage("dashboard")}><span>▦</span> Dashboard</button>
        <button className={`nav-link ${page === "history" ? "active" : ""}`} onClick={() => setPage("history")}><span>◷</span> Riwayat Booking</button>
        <div className="sidebar-note"><div className="note-icon">✦</div><strong>Butuh kendaraan?</strong><p>Pilih kendaraan yang tersedia dan lengkapi formulir peminjaman.</p></div>
        <div className="sidebar-footer">Car Pool Management<br /><span>Draft tampilan pengguna</span></div>
      </aside>

      <main className="main-content">
        <header className="topbar">
          <div className="breadcrumb">Car Pool <span>/</span> {page === "dashboard" ? "Dashboard" : page === "history" ? "Riwayat Booking" : "Form Peminjaman"}</div>
          <div className="profile"><div className="avatar">U</div><div><strong>Pengguna</strong><small>Akses umum</small></div></div>
        </header>

        {page === "dashboard" && (
          <section className="page-content">
            <div className="welcome-panel">
              <div>
                <span className="eyebrow">LAYANAN PEMINJAMAN KENDARAAN</span>
                <h1>Temukan kendaraan<br />untuk kebutuhanmu.</h1>
                <p>Pilih kendaraan yang tersedia, isi formulir peminjaman, lalu unduh dokumen untuk proses tanda tangan.</p>
                <button className="primary-button" onClick={() => document.getElementById("vehicle-list")?.scrollIntoView({ behavior: "smooth" })}>Lihat Kendaraan <span>→</span></button>
              </div>
              <div className="welcome-art"><div className="art-circle"></div><div className="art-car">🚙</div><div className="art-tag">Mudah · Praktis · Terdata</div></div>
            </div>

            <div className="section-heading">
              <div><span className="eyebrow">PILIH SESUAI KEBUTUHAN</span><h2>Kendaraan tersedia</h2><p>Berikut kendaraan yang dapat diajukan untuk peminjaman.</p></div>
              <div className="available-count"><strong>{vehicles.length}</strong><span>Kendaraan<br />tersedia</span></div>
            </div>

            <div className="toolbar" id="vehicle-list">
              <div className="search-box"><span>⌕</span><input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari nama atau nomor kendaraan..." /></div>
              <span className="availability-label"><i></i> Tersedia</span>
            </div>

            <div className="vehicle-grid">
              {filteredVehicles.map((vehicle) => (
                <article
                  className="vehicle-card"
                  key={vehicle.id}
                  onClick={() => openBooking(vehicle)}
                  role="button"
                  tabIndex={0}
                  onKeyDown={(e) => {
                    if (e.key === "Enter" || e.key === " ") {
                      e.preventDefault();
                      openBooking(vehicle);
                    }
                  }}>
                  <div className="vehicle-image"><img src={vehicle.image} alt={vehicle.name} /><span className="status-pill"><i></i> Available</span></div>
                  <div className="vehicle-info">
                    <div className="vehicle-type">{vehicle.type}</div>
                    <h3>{vehicle.name}</h3>
                    <p className="plate">{vehicle.plate}</p>
                    <div className="vehicle-specs"><span>♙ {vehicle.seats} penumpang</span><span>⚙ {vehicle.transmission}</span></div>
                    <button className="outline-button" onClick={() => openBooking(vehicle)}>Pilih Kendaraan <span>→</span></button>
                  </div>
                </article>
              ))}
              {filteredVehicles.length === 0 && <div className="empty-state">Kendaraan tidak ditemukan. Coba kata kunci lain.</div>}
            </div>
          </section>
        )}

        {page === "booking" && selectedVehicle && (
          <section className="page-content narrow-content">
            <span className="eyebrow">PENGAJUAN PEMINJAMAN</span>

            <h1 className="page-title">Formulir peminjaman</h1>

            <p className="page-description">
              Lengkapi data berikut untuk kendaraan{" "}
              <strong>{selectedVehicle.name}</strong> ({selectedVehicle.plate}).
            </p>

            <form className="booking-form" onSubmit={submitBooking}>
              <div className="form-grid">

                {/* Identitas Peminjam */}
                <label>
                  Nama peminjam
                  <input
                    required
                    value={booking.full_name}
                    onChange={(e) =>
                      setBooking({ ...booking, full_name: e.target.value })
                    }
                    placeholder="Masukkan nama lengkap"
                  />
                </label>

                <label>
                  Nomor pegawai (NIP)
                  <input
                    value={booking.employee_number}
                    onChange={(e) =>
                      setBooking({ ...booking, employee_number: e.target.value })
                    }
                    placeholder="Masukkan nomor pegawai"
                  />
                </label>

                <label>
                  Unit / Bagian
                  <input
                    required
                    value={booking.department}
                    onChange={(e) =>
                      setBooking({ ...booking, department: e.target.value })
                    }
                    placeholder="Masukkan unit atau bagian"
                  />
                </label>

                <label>
                  Nomor telepon
                  <input
                    required
                    type="tel"
                    value={booking.phone_number}
                    onChange={(e) =>
                      setBooking({ ...booking, phone_number: e.target.value })
                    }
                    placeholder="Masukkan nomor telepon"
                  />
                </label>

                {/* Detail Peminjaman */}
                <label className="full-width">
                  Tujuan peminjaman
                  <input
                    required
                    value={booking.destination}
                    onChange={(e) =>
                      setBooking({ ...booking, destination: e.target.value })
                    }
                    placeholder="Masukkan lokasi atau tujuan perjalanan"
                  />
                </label>

                <label className="full-width">
                  Keperluan peminjaman
                  <textarea
                    required
                    value={booking.purpose}
                    onChange={(e) =>
                      setBooking({ ...booking, purpose: e.target.value })
                    }
                    placeholder="Jelaskan keperluan peminjaman"
                  />
                </label>

                <label>
                  Jumlah penumpang
                  <input
                    required
                    type="number"
                    min="1"
                    max={selectedVehicle.seats}
                    value={booking.passenger_count}
                    onChange={(e) =>
                      setBooking({ ...booking, passenger_count: e.target.value })
                    }
                  />
                </label>

                <label>
                  Nama pengemudi
                  <input
                    required
                    value={booking.driver_name}
                    onChange={(e) =>
                      setBooking({ ...booking, driver_name: e.target.value })
                    }
                    placeholder="Masukkan nama pengemudi"
                  />
                </label>

                {/* Jadwal Peminjaman */}
                <label>
                  Tanggal dan waktu mulai
                  <input
                    required
                    type="datetime-local"
                    value={booking.start_datetime}
                    onChange={(e) =>
                      setBooking({
                        ...booking,
                        start_datetime: e.target.value,
                      })
                    }
                  />
                </label>

                <label>
                  Tanggal dan waktu selesai
                  <input
                    required
                    type="datetime-local"
                    min={booking.start_datetime}
                    value={booking.end_datetime}
                    onChange={(e) =>
                      setBooking({
                        ...booking,
                        end_datetime: e.target.value,
                      })
                    }
                  />
                </label>
              </div>

              {errorMessage && (
                <div className="form-error">
                  {errorMessage}
                </div>
              )}

              <div className="form-actions">
                <button
                  type="button"
                  className="outline-button"
                  onClick={() => setPage("dashboard")}
                  disabled={submitting}
                >
                  Batal
                </button>

                <button
                  className="primary-button"
                  type="submit"
                  disabled={submitting}
                >
                  {submitting ? "Mengirim Pengajuan..." : "Ajukan Peminjaman →"}
                </button>
              </div>

              <p className="form-hint">
                Setelah pengajuan berhasil, formulir Model C akan diunduh
                secara otomatis. Cetak formulir untuk proses tanda tangan.
              </p>
            </form>
          </section>
        )}

        {page === "history" && (
          <section className="page-content">
            <span className="eyebrow">ARSIP PENGAJUAN</span><h1 className="page-title">Riwayat Booking</h1>
            <p className="page-description">Daftar formulir peminjaman yang pernah dibuat.</p>
            {history.length === 0 ? <div className="empty-panel"><div className="empty-icon">▤</div><h3>Belum ada riwayat booking</h3><p>Formulir yang kamu buat akan muncul di halaman ini.</p><button
              className="outline-button"
              onClick={() => {
                const link = document.createElement("a");
                link.href = item.downloadUrl;
                link.click();
              }}
            >
              Unduh Model C
            </button></div> :
              <div className="history-list">{history.map((item) => <article className="history-card" key={item.id}><div className="history-car">🚙</div><div className="history-detail"><strong>{item.vehicle.name}</strong><span>{item.vehicle.plate} · {item.name}</span><small>{item.startDate} sampai {item.endDate}</small></div><button className="outline-button" onClick={() => window.print()}>Cetak / Unduh</button></article>)}</div>}
          </section>
        )}
      </main>
    </div>
  );
}
