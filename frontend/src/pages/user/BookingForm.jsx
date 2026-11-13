export default function BookingForm({
  selectedVehicle, booking, setBooking, submitBooking, submitting, errorMessage, cancelBooking,
}) {
  return (
        <section className="page-content narrow-content">
            <span className="eyebrow">PENGAJUAN PEMINJAMAN</span>

            <h1 className="page-title">Formulir peminjaman</h1>

            <p className="page-description">
              Lengkapi data berikut untuk kendaraan{" "}
              <strong>{selectedVehicle.brand}</strong> ({selectedVehicle.license_plate}).
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
                  onClick={cancelBooking}
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
                Setelah pengajuan berhasil, unduh formulir Model C melalui
                tombol yang tersedia pada halaman Riwayat Booking.
              </p>
            </form>
    </section>
  );
}
