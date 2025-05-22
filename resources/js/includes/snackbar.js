class Snackbar extends HTMLElement {
	constructor() {
		super();

		// Attach shadow DOM to the custom element
		const shadow = this.attachShadow({ mode: 'open' });

		// Add styles to shadow DOM
		const styles = document.createElement('style');
		styles.textContent = `
            :host {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                height: max(5rem, 20%);
                display: flex;
                align-items: center;
                justify-content: center;
				pointer-events: none;
			   z-index: 10000000;
            }

            .snackbar {
                min-width: 320px;
                max-width: 600px;
                display:none;
                padding: 16px;
                background-color: #323232;
                color: #ffffff;
                text-align: center;
                border-radius: 4px;
                box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
                animation: fadein 0.5s;
				pointer-events: auto;
            }

            .snackbar.error {
                background-color: #9d0000;
            }

            @keyframes fadein {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        `;

		// Create the snackbar
		const snackbar = document.createElement('div');
		snackbar.classList.add('snackbar');
		snackbar.textContent = 'This is a custom snackbar!';

		// Append style and content to shadow DOM
		shadow.appendChild(styles);
		shadow.appendChild(snackbar);
	}
}

// Define the custom element
customElements.define('dropp-snackbar', Snackbar);

export default function () {
	// If the overlay already exists, remove it
	const existingOverlay = document.querySelector('dropp-snackbar');
	if (existingOverlay) {
		existingOverlay.remove();
	}

	// Append a new overlay element to the body
	const overlay = document.createElement('dropp-snackbar');
	document.body.appendChild(overlay);
	const snackbar = overlay.shadowRoot.querySelector('.snackbar');

	// Utility to manage timeouts and visibility
	const SnackbarManager = {
		currentTimeout: null,
		visibilityHandlerAdded: false,
		showSnackbar: (message) => {
			// Clear any existing timeout
			SnackbarManager.clearTimeout();

			// Set message and show the snackbar
			snackbar.textContent = message;
			snackbar.classList.remove('error');
			snackbar.style.display = 'block';

			// Start the timeout only if the page is visible
			if (! document.hidden) {
				SnackbarManager.startTimeout();
			}
			if (! SnackbarManager.visibilityHandlerAdded) {
				document.addEventListener('visibilitychange', SnackbarManager.visibilityHandler);
				SnackbarManager.visibilityHandlerAdded = true;
			}
		},

		startTimeout: () => {
			SnackbarManager.currentTimeout = setTimeout(() => {
				SnackbarManager.hideSnackbar();
			}, 5000);
		},

		hideSnackbar: () => {
			snackbar.style.display = 'none';
			SnackbarManager.clearListeners();
			SnackbarManager.clearTimeout();
		},

		visibilityHandler: () => {
			if (document.hidden) {
				SnackbarManager.clearTimeout();
			} else {
				SnackbarManager.startTimeout();
			}
		},

		clearTimeout: () => {
			if (SnackbarManager.currentTimeout) {
				clearTimeout(SnackbarManager.currentTimeout);
				SnackbarManager.currentTimeout = null;
			}
		},

		clearListeners: () => {
			if (! SnackbarManager.visibilityHandlerAdded) {
				return;
			}
			document.removeEventListener('visibilitychange', SnackbarManager.visibilityHandler);
			SnackbarManager.visibilityHandlerAdded = false;
		}
	};

	return {
		close: () => {
			SnackbarManager.clearListeners();
			overlay.remove();
		},
		snack: (message) => {
			SnackbarManager.showSnackbar(message);
			return {
				error: () => snackbar.classList.add('error')
			};
		}
	};
}
