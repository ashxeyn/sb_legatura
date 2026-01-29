document.addEventListener('DOMContentLoaded', () => {
    const editBtn = document.getElementById('editBtn');
    const selectBtn = document.getElementById('selectBtn');
    const skipBtn = document.getElementById('skipBtn');
    const input = document.getElementById('avatarInput');
    const avatarImg = document.getElementById('avatarImg');

    const triggerPicker = () => input?.click();

    const handleFileChange = (event) => {
        const file = event.target.files?.[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = (e) => {
            if (avatarImg) {
                avatarImg.src = e.target?.result;
            }
        };
        reader.readAsDataURL(file);
    };

    const handleSkip = () => {
        window.location.href = '/';
    };

    editBtn?.addEventListener('click', triggerPicker);
    selectBtn?.addEventListener('click', triggerPicker);
    input?.addEventListener('change', handleFileChange);
    skipBtn?.addEventListener('click', handleSkip);
});
