document.addEventListener('DOMContentLoaded', () => {
	const cards = Array.from(document.querySelectorAll('.role-card-only'));
	const hidden = document.getElementById('selectedRole');
	const btn = document.getElementById('continueBtn');

	const selectRole = (role) => {
		cards.forEach((card) => {
			const isSelected = card.dataset.role === role;
			card.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
		});
		hidden.value = role;
		btn.disabled = !role;
	};

	cards.forEach((card) => {
		card.addEventListener('click', () => selectRole(card.dataset.role));
		card.addEventListener('keydown', (e) => {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				selectRole(card.dataset.role);
			}
		});
		card.setAttribute('tabindex', '0');
	});

	// Handle continue button click
	btn.addEventListener('click', () => {
		const selectedRole = hidden.value;
		if (selectedRole === 'owner') {
			window.location.href = '/propertyOwner/account-setup';
		} else if (selectedRole === 'contractor') {
			window.location.href = '/contractor/account-setup';
		}
	});
});
