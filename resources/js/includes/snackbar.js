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
    display: none;
    padding: 16px;
    background-color: #323232;
    color: #ffffff;
    text-align: center;
    border-radius: 4px;
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.2);
    transition: opacity 0.5s ease, transform 0.5s ease; /* Transition for smooth fade */
    transform: translateY(0);
    opacity: 1;
    pointer-events: auto;
}

.snackbar.fade-in {
    display: block;
    opacity: 1;
    transform: translateY(0);
}

.snackbar.fade-out {
    opacity: 0;
    transform: translateY(30px); /* Move slightly downwards while fading out */
    pointer-events: none;
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
		fadeTimeout: null,
		visibilityHandlerAdded: false,

		showSnackbar: (message) => {
			// Clear any existing timeout or fade-out states
			SnackbarManager.clearTimeout();
			SnackbarManager.cancelFadeOut();

			// Set message and show the snackbar with fade-in
			snackbar.textContent = message;
			snackbar.classList.remove('error', 'fade-out');
			snackbar.classList.add('fade-in');
			snackbar.style.display = 'block';

			// Start the timeout only if the page is visible
			if (!document.hidden) {
				SnackbarManager.startTimeout();
			}
			if (!SnackbarManager.visibilityHandlerAdded) {
				document.addEventListener('visibilitychange', SnackbarManager.visibilityHandler);
				SnackbarManager.visibilityHandlerAdded = true;
			}
		},

		startTimeout: () => {
			// After the snackbar is shown, schedule it for hiding
			SnackbarManager.currentTimeout = setTimeout(() => {
				SnackbarManager.fadeOutSnackbar();
			}, 5000); // 5 seconds timeout
		},

		fadeOutSnackbar: () => {
			// Add CSS fade-out class
			snackbar.classList.remove('fade-in');
			snackbar.classList.add('fade-out');

			// Use a timeout corresponding to the fade-out duration (in ms) to hide the snackbar
			SnackbarManager.fadeTimeout = setTimeout(() => {
				snackbar.style.display = 'none';
				SnackbarManager.clearListeners();
			}, 500); // Match this to the CSS animation duration for the fade-out
		},

		cancelFadeOut: () => {
			// Stop any ongoing fade-out process
			if (SnackbarManager.fadeTimeout) {
				clearTimeout(SnackbarManager.fadeTimeout);
				SnackbarManager.fadeTimeout = null;
			}
			// Reset fade-out class (so new snack behaves correctly)
			snackbar.classList.remove('fade-out');
		},

		hideSnackbar: () => {
			// Remove snackbar immediately (used for full reset)
			snackbar.style.display = 'none';
			SnackbarManager.clearListeners();
			SnackbarManager.clearTimeout();
		},

		visibilityHandler: () => {
			// Pause timeout if the page is hidden
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
			if (SnackbarManager.visibilityHandlerAdded) {
				document.removeEventListener('visibilitychange', SnackbarManager.visibilityHandler);
				SnackbarManager.visibilityHandlerAdded = false;
			}
		}
	};

	return {
		close: () => {
			SnackbarManager.hideSnackbar();
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
