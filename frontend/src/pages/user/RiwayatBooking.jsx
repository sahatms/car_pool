export default function RiwayatBooking({ history }) {
  return (
<section className="page-content">
            <span className="eyebrow">ARSIP PENGAJUAN</span>
            <h1 className="page-title">Riwayat Booking</h1>
            <p className="page-description">
              Daftar formulir peminjaman yang pernah dibuat.
            </p>

            {history.length === 0 ? (
              <div className="empty-panel">
                <div className="empty-icon">▤</div>
                <h3>Belum ada riwayat booking</h3>
                <p>Formulir yang kamu buat akan muncul di halaman ini.</p>
              </div>
            ) : (
              <div className="history-list">
                {history.map((item) => (
                  <article className="history-card" key={item.id}>
                    <div className="history-car">🚙</div>

                    <div className="history-detail">
                      <strong>{item.vehicle.brand || item.vehicle.name}</strong>
                      <span>
                        {item.vehicle.license_plate || item.vehicle.plate}
                        {" · "}
                        {item.name}
                      </span>
                      <small>
                        {item.startDate} sampai {item.endDate}
                      </small>
                    </div>

                    <button
                      className="outline-button"
                      onClick={() => {
                        const link = document.createElement("a");
                        link.href = item.downloadUrl;
                        link.target = "_blank";
                        link.rel = "noopener noreferrer";
                        link.click();
                      }}
                      disabled={!item.downloadUrl}
                    >
                      Unduh Model C
                    </button>
                  </article>
                ))}
              </div>
            )}
          </section>
  );
}
