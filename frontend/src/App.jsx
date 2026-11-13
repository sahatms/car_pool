import { useEffect, useMemo, useState } from "react";
import "./App.css";
import UserLayout from "./layouts/UserLayout";
import DashboardUser from "./pages/user/DashboardUser";
import BookingForm from "./pages/user/BookingForm";
import RiwayatBooking from "./pages/user/RiwayatBooking";
import { getVehicles, createBooking } from "./services/api";

function getVehicleStatus(status) {
  const statuses = {
    active: "Tersedia",
    booked: "Sedang Dipinjam",
    inactive: "Tidak Aktif",
  };
  return statuses[status] || status;
}

export default function App() {
  const [page, setPage] = useState("dashboard");
  const [selectedVehicle, setSelectedVehicle] = useState(null);
  const [search, setSearch] = useState("");
  const [booking, setBooking] = useState({
    full_name: "", employee_number: "", department: "", phone_number: "",
    destination: "", purpose: "", passenger_count: "", driver_name: "",
    start_datetime: "", end_datetime: "",
  });
  const [submitting, setSubmitting] = useState(false);
  const [errorMessage, setErrorMessage] = useState("");
  const [history, setHistory] = useState([]);
  const [vehicles, setVehicles] = useState([]);
  const [loadingVehicles, setLoadingVehicles] = useState(true);
  const [vehicleError, setVehicleError] = useState("");

  useEffect(() => {
    async function fetchVehicleData() {
      try {
        setLoadingVehicles(true);
        setVehicleError("");
        const data = await getVehicles();
        setVehicles(data);
      } catch (error) {
        setVehicleError("Tidak dapat mengambil data kendaraan. Pastikan server Laravel berjalan.");
      } finally {
        setLoadingVehicles(false);
      }
    }
    fetchVehicleData();
  }, []);

  const filteredVehicles = useMemo(() =>
    vehicles.filter((vehicle) =>
      `${vehicle.brand} ${vehicle.license_plate}`.toLowerCase().includes(search.toLowerCase())
    ), [vehicles, search]);

  const availableCount = vehicles.filter((vehicle) => vehicle.status === "active").length;

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
      const result = await createBooking({
        ...booking,
        vehicle_id: selectedVehicle.id,
        passenger_count: Number(booking.passenger_count),
      });

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
      setPage("history");
      setBooking({
        full_name: "", employee_number: "", department: "", phone_number: "",
        destination: "", purpose: "", passenger_count: 1, driver_name: "",
        start_datetime: "", end_datetime: "",
      });
    } catch (error) {
      setErrorMessage(error.message);
    } finally {
      setSubmitting(false);
    }
  }

  function cancelBooking() {
    setErrorMessage("");
    setPage("dashboard");
  }

  return (
    <UserLayout page={page} setPage={setPage}>
      {page === "dashboard" && (
        <DashboardUser
          filteredVehicles={filteredVehicles}
          loadingVehicles={loadingVehicles}
          vehicleError={vehicleError}
          availableCount={availableCount}
          search={search}
          setSearch={setSearch}
          openBooking={openBooking}
          getVehicleStatus={getVehicleStatus}
        />
      )}
      {page === "booking" && selectedVehicle && (
        <BookingForm
          selectedVehicle={selectedVehicle}
          booking={booking}
          setBooking={setBooking}
          submitBooking={submitBooking}
          submitting={submitting}
          errorMessage={errorMessage}
          cancelBooking={cancelBooking}
        />
      )}
      {page === "history" && <RiwayatBooking history={history} />}
    </UserLayout>
  );
}
