export const API_URL = "http://127.0.0.1:8000/api";

export async function getVehicles() {
  const response = await fetch(`${API_URL}/vehicles`, {
    headers: { Accept: "application/json" },
  });

  if (!response.ok) {
    throw new Error("Gagal mengambil data kendaraan.");
  }

  const result = await response.json();
  return result.data || [];
}

export async function createBooking(payload) {
  const response = await fetch(`${API_URL}/bookings`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
    },
    body: JSON.stringify(payload),
  });

  const result = await response.json();

  if (!response.ok) {
    const validationErrors = result.errors
      ? Object.values(result.errors).flat().join("\n")
      : result.message;
    throw new Error(validationErrors || "Pengajuan booking gagal.");
  }

  return result;
}
