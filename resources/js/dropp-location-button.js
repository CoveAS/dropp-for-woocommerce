class DroppLocationButton extends HTMLElement {
	connectedCallback() {
		this.attachShadow({ mode: 'open' });

		this.shadowRoot.innerHTML = `
			<style>
				:host {
					display: inline-block;
				}
				button {
					display: inline-flex;
					align-items: center;
					gap: 0.4em;
					padding: 0.45em 1em;
					font-size: 0.9em;
					font-family: inherit;
					font-weight: 600;
					line-height: 1.4;
					color: #ffffff;
					background-color: rgb(0, 0, 125);
					border: none;
					border-radius: 100px;
					cursor: pointer;
					white-space: nowrap;
					transition: background-color 0.15s ease;
				}
				button:hover {
					background-color: rgb(0, 0, 175);
				}
				button:focus-visible {
					outline: 2px solid rgb(0, 0, 125);
					outline-offset: 2px;
				}
				.icon {
					width: 1em;
					height: 1em;
					flex-shrink: 0;
				}
			</style>
			<button type="button" part="button">
				<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
					<path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/>
					<circle cx="12" cy="10" r="3"/>
				</svg>
				<slot>${this.getAttribute('label') || 'Choose location'}</slot>
			</button>
		`;

		this.shadowRoot.querySelector('button').addEventListener('click', (e) => {
			e.preventDefault();
			this.dispatchEvent(new CustomEvent('dropp-choose', { bubbles: true, composed: true }));
		});
	}
}

customElements.define('dropp-location-button', DroppLocationButton);
