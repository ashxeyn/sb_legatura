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
            showToastMsg('Please select an image file', 'error');
            return;
        }

        // Validate file size (max 5MB)
        if (file.size > 5 * 1024 * 1024) {
            showToastMsg('File size must be less than 5MB', 'error');
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
                showSuccessModal();
            } else {
                showToastMsg(data.message || 'Registration failed. Please try again.', 'error');
                continueBtn.disabled = false;
                skipBtn.disabled = false;
                continueBtn.textContent = 'Continue';
            }
        } catch (error) {
            console.error('Error:', error);
            showToastMsg('Network error. Please check your connection and try again.', 'error');
            continueBtn.disabled = false;
            skipBtn.disabled = false;
            continueBtn.textContent = 'Continue';
        }
    };

    const handleSubmit = async (event) => {
        event.preventDefault();

        continueBtn.disabled = true;
        skipBtn.disabled = true;
        continueBtn.textContent = 'Processing...';

        try {
            const formData = new FormData(profileForm);

            // Debug: Log form data being sent
            console.log('Form data being submitted:');
            for (let [key, value] of formData.entries()) {
                if (key.includes('_data')) {
                    try {
                        console.log(`  ${key}:`, JSON.parse(value));
                    } catch {
                        console.log(`  ${key}:`, value);
                    }
                } else {
                    console.log(`  ${key}:`, value?.name || value);
                }
            }

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
                showToastMsg(data.message || 'Registration failed. Please try again.', 'error');
                continueBtn.disabled = false;
                skipBtn.disabled = false;
                continueBtn.textContent = 'Continue';
            }
        } catch (error) {
            console.error('Error:', error);
            showToastMsg('Network error. Please check your connection and try again.', 'error');
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

    // Toast helper
    function showToastMsg(message, type = 'error') {
        let toast = document.getElementById('toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'toast';
            toast.style.cssText = 'position:fixed;top:20px;right:20px;padding:16px 24px;border-radius:8px;color:#fff;z-index:9999;font-size:14px;max-width:300px;box-shadow:0 4px 12px rgba(0,0,0,0.15);';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.background = type === 'error' ? '#f44336' : type === 'success' ? '#4caf50' : '#2196f3';
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 4000);
    }

    // Success modal for admin approval
    function showSuccessModal() {
        let modal = document.getElementById('registrationSuccessModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'registrationSuccessModal';
            modal.style.cssText = 'position:fixed;inset:0;background:rgba(0,0,0,0.6);display:flex;align-items:center;justify-content:center;z-index:10000;backdrop-filter:blur(4px);';
            modal.innerHTML = `
                <div style="background:#fff;border-radius:16px;padding:48px 36px;max-width:420px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.3);animation:scaleIn 0.3s ease;">
                    <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#4caf50,#66bb6a);display:flex;align-items:center;justify-content:center;margin:0 auto 24px;">
                        <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <h2 style="font-size:22px;font-weight:700;color:#1a1a1a;margin:0 0 12px;">Registration Complete!</h2>
                    <p style="font-size:15px;color:#555;line-height:1.6;margin:0 0 28px;">Your registration has been submitted successfully. Please wait for admin approval and check your email for further instructions before you can use your account.</p>
                    <button id="successModalLoginBtn" style="background:linear-gradient(135deg,#f57c00,#ff9800);color:#fff;border:none;padding:14px 36px;border-radius:10px;font-size:16px;font-weight:600;cursor:pointer;transition:transform 0.2s,box-shadow 0.2s;box-shadow:0 4px 15px rgba(245,124,0,0.4);">
                        Understood
                    </button>
                </div>
            `;
            document.body.appendChild(modal);

            // Add animation keyframe
            if (!document.getElementById('scaleInStyle')) {
                const style = document.createElement('style');
                style.id = 'scaleInStyle';
                style.textContent = '@keyframes scaleIn{from{transform:scale(0.8);opacity:0}to{transform:scale(1);opacity:1}}';
                document.head.appendChild(style);
            }
        }
        modal.style.display = 'flex';
        const loginBtn = document.getElementById('successModalLoginBtn');
        if (loginBtn) {
            loginBtn.onclick = () => { window.location.href = '/'; };
            loginBtn.onmouseenter = () => { loginBtn.style.transform = 'translateY(-2px)'; loginBtn.style.boxShadow = '0 6px 20px rgba(245,124,0,0.5)'; };
            loginBtn.onmouseleave = () => { loginBtn.style.transform = 'translateY(0)'; loginBtn.style.boxShadow = '0 4px 15px rgba(245,124,0,0.4)'; };
        }
    }
});
