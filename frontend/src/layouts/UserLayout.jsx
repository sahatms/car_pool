export default function UserLayout({ page, setPage, children }) {
  const pageTitle = page === "dashboard" ? "Dashboard" : page === "history" ? "Riwayat Booking" : "Form Peminjaman";

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
          <div className="breadcrumb">Car Pool <span>/</span> {pageTitle}</div>
          <div className="profile"><div className="avatar">U</div><div><strong>Pengguna</strong><small>Akses umum</small></div></div>
        </header>
        {children}
      </main>
    </div>
  );
}
