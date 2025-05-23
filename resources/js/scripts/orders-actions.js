document.addEventListener('DOMContentLoaded', () => {
	const list = document.getElementById('the-list');
	if (!list || !list.dataset.wpLists?.includes('list:order')) return;

	document.querySelectorAll('.dropp-shipment-book-link').forEach(button => {
		button.addEventListener('click', (e) => {
			e.preventDefault();
			handleShipmentButton(button);
		});
	});
});


let loading = false;
/**
 * Handles the "Book Shipment" button click, sending the order ID via AJAX
 * and updating the table row cells upon success.
 *
 * @param {HTMLElement} button - The clicked shipment button.
 */
function handleShipmentButton(button) {
	const orderId = button.dataset.orderId;

	if (loading) {
		return;
	}
	if (!orderId) {
		console.error('Order ID not found on the button element.');
		return;
	}

	const formData = new FormData();
	formData.append('action', 'dropp_book_order');
	formData.append('order_id', orderId);
	formData.append('dropp_nonce', _dropp.nonce);

	loading = true;
	fetch(_dropp.ajaxurl, { method: 'POST', body: formData })
		.then(response => validateResponse(response))
        .then(data => {
			loading = false;
			const message = data.message;
            // Validate the response status
            if (data.status !== 'success') {
				console.error('Error in response:', data);
				if (! message) {
					message = 'An unknown error occured';
				}
				window._dropp.snack(message).error()
				return;
			}
			// Update the order cells
			updateOrderCells(data, orderId);

			// Focus pdf button
			const pdfButton = document.querySelector(`tr.order-${orderId} td.pdf_column .dropp-button`);
			if (pdfButton) {
				pdfButton.focus();
			}

			// Automatically open the URL in a new tab (if provided)
			if (data.url) {
				window.open(data.url, '_blank');
			}

			window._dropp.snack(message)
        })
		.catch(
			error => {
				window._dropp.snack(error).error()
				loading = false;
			}
		);
}

/**
 * Validates the server response and parses it as JSON.
 *
 * @param {Response} response - The fetch response object.
 * @returns {Object} Parsed JSON data.
 * @throws {Error} When response is not OK.
 */
async function validateResponse(response) {
	if (!response.ok) {
		// Attempt to parse the response body as JSON for better error messaging
		let errorMessage = `HTTP error! status: ${response.status}`;
		try {
			const errorData = await response.json();
			if (errorData.message) {
				errorMessage = errorData.message; // Use the message property if it exists
			}
		} catch {}
		throw new Error(errorMessage);
	}
	return response.json();
}

/**
 * Updates the HTML content of the shipment and PDF columns for the order.
 *
 * @param {Object} data - The response data from the server.
 * @param {string} orderId - The ID of the order being updated.
 */
function updateOrderCells(data, orderId) {
	const html = data.html;

    if (!html || typeof html.shipment_column === 'undefined' || typeof html.pdf_column === 'undefined' || typeof html.order_status === 'undefined') {
        console.error('Response is missing the expected "html" object or columns', data);
        return;
    }

    // Select and update the related cells for the given orderId
    const shipmentCell = document.querySelector(`tr.order-${orderId} td.shipment_column`);
    const pdfCell = document.querySelector(`tr.order-${orderId} td.pdf_column`);
	const statusCell = document.querySelector(`tr.order-${orderId} td.order_status`);

    if (shipmentCell) shipmentCell.innerHTML = html.shipment_column;
    if (pdfCell) pdfCell.innerHTML = html.pdf_column;
	if (statusCell) statusCell.innerHTML = html.order_status;
}
