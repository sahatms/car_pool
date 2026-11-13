export default function DashboardUser({
  filteredVehicles, loadingVehicles, vehicleError, availableCount,
  search, setSearch, openBooking, getVehicleStatus,
}) {
  return (
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
              <div className="available-count"><strong>{availableCount}</strong><span>Kendaraan<br />tersedia</span></div>
            </div>

            <div className="toolbar" id="vehicle-list">
              <div className="search-box"><span>⌕</span><input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari nama atau nomor kendaraan..." /></div>
              <span className="availability-label"><i></i> Tersedia</span>
            </div>

            <div className="vehicle-grid">
              {loadingVehicles ? (
                <div className="empty-state">
                  Memuat data kendaraan...
                </div>
              ) : vehicleError ? (
                <div className="empty-state">
                  {vehicleError}
                </div>
              ) : filteredVehicles.length === 0 ? (
                <div className="empty-state">
                  Kendaraan tidak ditemukan.
                </div>
              ) : (
                filteredVehicles.map((vehicle) => {
                  const isSelectable = vehicle.status === "active";

                  const photoUrl = vehicle.photo
                    ? vehicle.photo.startsWith("http")
                      ? vehicle.photo
                      : `http://127.0.0.1:8000/storage/${vehicle.photo}`
                    : null;

                  return (
                    <article
                      className={`vehicle-card ${!isSelectable ? "disabled" : ""}`}
                      key={vehicle.id}
                      onClick={() => {
                        if (isSelectable) openBooking(vehicle);
                      }}
                      role={isSelectable ? "button" : undefined}
                      tabIndex={isSelectable ? 0 : -1}
                      aria-disabled={!isSelectable}
                      onKeyDown={(e) => {
                        if (
                          isSelectable &&
                          (e.key === "Enter" || e.key === " ")
                        ) {
                          e.preventDefault();
                          openBooking(vehicle);
                        }
                      }}
                    >
                      <div className="vehicle-image">
                        {photoUrl ? (
                          <img src={photoUrl} alt={vehicle.brand} />
                        ) : (
                          <div className="vehicle-placeholder">
                            🚙
                          </div>
                        )}

                        <span
                          className={`status-pill status-${vehicle.status}`}
                        >
                          <i></i>
                          {getVehicleStatus(vehicle.status)}
                        </span>
                      </div>

                      <div className="vehicle-info">

                        <h3>{vehicle.brand}</h3>

                        <p className="plate">
                          {vehicle.license_plate}
                        </p>

                        <div className="vehicle-specs">
                          <span>
                            {vehicle.vehicle_type === "2_wheels"
                              ? "Roda 2"
                              : vehicle.vehicle_type === "4_wheels"
                                ? "Roda 4"
                                : vehicle.vehicle_type === "6_wheels"
                                  ? "Roda 6"
                                  : vehicle.vehicle_type}
                          </span>
                        </div>

                        <button
                          type="button"
                          className="outline-button"
                          disabled={!isSelectable}
                          onClick={(e) => {
                            e.stopPropagation();
                            if (isSelectable) openBooking(vehicle);
                          }}
                        >
                          {isSelectable
                            ? "Pilih Kendaraan →"
                            : "Tidak Tersedia"}
                        </button>
                      </div>
                    </article>
                  );
                })
              )}
            </div>
          </section>
  );
}
