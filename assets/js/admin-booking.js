document.addEventListener("DOMContentLoaded", () => {

        const searchInput = document.getElementById("searchBooking");
        const statusFilter = document.getElementById("filterStatus");
        const dateFilter = document.getElementById("filterDate");
        const tableBody = document.getElementById("booking-table-body");

        const fetchBookings = () => {
            const search = searchInput.value;
            const status = statusFilter.value;
            const date = dateFilter.value;

            const params = new URLSearchParams({
                ajax: 1,
                search,
                status,
                date
            });
            fetch("admin_booking.php?" + params.toString())
                .then(res => res.json())
                .then(data => {
                    tableBody.innerHTML = "";

                    if (data.length === 0) {
                        tableBody.innerHTML = `<tr><td colspan="6" style="text-align:center;">No bookings found.</td></tr>`;
                        return;
                    }

                    data.forEach(b => {
                        const statusClass = b.status === "pending" ? "pending" : (b.status === "confirmed" ? "confirmed" : "cancelled");

                        const row = document.createElement("tr");
                        row.innerHTML = `
                        <td>${b.name}</td>
                        <td>${new Date(b.reservation_date).toLocaleDateString()}</td>
                        <td>${b.start_time} - ${b.end_time}</td>
                        <td>${b.type}</td>
                        <td><p class="status-pill ${statusClass}">${b.status.charAt(0).toUpperCase() + b.status.slice(1)}</p></td>
                        <td class="actions-td">
                            <button class="btn-view">View</button>
                            ${b.status === "pending" ? `<form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="${b.id}">
                                <input type="hidden" name="action" value="confirm">
                                <button class="btn-confirm">Confirm</button>
                            </form>` : ""}
                            ${b.status !== "cancelled" ? `<form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="${b.id}">
                                <input type="hidden" name="action" value="cancel">
                                <button class="btn-cancel">Cancel</button>
                            </form>` : ""}
                            ${(b.status === "confirmed" || b.status === "cancelled") ? `<form method="POST" style="display:inline;">
                                <input type="hidden" name="booking_id" value="${b.id}">
                                <input type="hidden" name="action" value="revert">
                                <button class="btn-revert">Revert</button>
                            </form>` : ""}
                        </td>
                    `;
                        tableBody.appendChild(row);
                    });
                });
        };

        // Event listeners
        searchInput.addEventListener("input", fetchBookings);
        statusFilter.addEventListener("change", fetchBookings);
        dateFilter.addEventListener("change", fetchBookings);

        // Load bookings initially
        fetchBookings();
    });