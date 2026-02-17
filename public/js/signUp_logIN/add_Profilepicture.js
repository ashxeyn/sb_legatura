document.addEventListener('DOMContentLoaded', () => {
    const editBtn = document.getElementById('editBtn');
    const continueBtn = document.getElementById('continueBtn');
    const skipBtn = document.getElementById('skipBtn');
    const input = document.getElementById('avatarInput');
    const avatarImg = document.getElementById('avatarImg');
    const avatarIcon = document.getElementById('avatarIcon');
    const avatarCircle = document.getElementById('avatarCircle');
    const profileForm = document.getElementById('profileForm');

    let hasProfilePic = false;

    const triggerPicker = () => input?.click();

    const handleFileChange = (event) => {
        const file = event.target.files?.[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            alert('Please select an image file');
            return;
        }

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('File size must be less than 5MB');
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            if (avatarImg && avatarIcon) {
                avatarImg.src = e.target.result;
                avatarImg.style.display = 'block';
                avatarIcon.style.display = 'none';
                hasProfilePic = true;
            }
        };
        reader.readAsDataURL(file);
    };

    const handleSkip = async () => {
        if (confirm('Are you sure you want to skip adding a profile picture? You can always add one later in your profile settings.')) {
            // Submit form without profile picture
            try {
                continueBtn.disabled = true;
                skipBtn.disabled = true;
                continueBtn.textContent = 'Processing...';

                const formData = new FormData(profileForm);
                // Remove the profile_pic file from formData if present
                formData.delete('profile_pic');

                const response = await fetch(profileForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert('Registration completed successfully! Please login to continue.');
                    window.location.href = data.redirect || '/login';
                } else {
                    alert(data.message || 'Registration failed. Please try again.');
                    continueBtn.disabled = false;
                    skipBtn.disabled = false;
                    continueBtn.textContent = 'Continue';
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Network error. Please check your connection and try again.');
                continueBtn.disabled = false;
                skipBtn.disabled = false;
                continueBtn.textContent = 'Continue';
            }
        }
    };

    const handleSubmit = async (event) => {
        event.preventDefault();

        continueBtn.disabled = true;
        skipBtn.disabled = true;
        continueBtn.textContent = 'Processing...';

        try {
            const formData = new FormData(profileForm);

            const response = await fetch(profileForm.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Show success overlay
                const successOverlay = document.getElementById('successOverlay');
                if (successOverlay) {
                    successOverlay.style.display = 'flex';
                }
                // Redirect to login after 2 seconds
                setTimeout(() => {
                    window.location.href = data.redirect || '/accounts/login';
                }, 2000);
            } else {
                alert(data.message || 'Registration failed. Please try again.');
                continueBtn.disabled = false;
                skipBtn.disabled = false;
                continueBtn.textContent = 'Continue';
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Network error. Please check your connection and try again.');
            continueBtn.disabled = false;
            skipBtn.disabled = false;
            continueBtn.textContent = 'Continue';
        }
    };

    editBtn?.addEventListener('click', triggerPicker);
    // make avatar circle clickable and keyboard-accessible
    if (avatarCircle) {
        avatarCircle.setAttribute('tabindex', '0');
        avatarCircle.setAttribute('role', 'button');
        avatarCircle.addEventListener('click', triggerPicker);
        avatarCircle.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                triggerPicker();
            }
        });
    }

    input?.addEventListener('change', handleFileChange);
    skipBtn?.addEventListener('click', handleSkip);
    profileForm?.addEventListener('submit', handleSubmit);
});
