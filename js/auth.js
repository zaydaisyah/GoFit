const Auth = {
    // Check if user is logged in. If not, redirect to login page.
    checkAuth: function () {
        const isLoggedIn = localStorage.getItem('isLoggedIn');
        if (!isLoggedIn) {
            const currentPage = window.location.pathname.split('/').pop();
            window.location.href = `login.php?redirect=${currentPage}`;
        }
    },

    // Sync localStorage with server session
    syncWithServer: function () {
        return fetch('api/check_session.php')
            .then(res => res.json())
            .then(data => {
                if (data.isLoggedIn) {
                    localStorage.setItem('isLoggedIn', 'true');
                    localStorage.setItem('gofit_user_profile', JSON.stringify(data.user));
                } else {
                    localStorage.removeItem('isLoggedIn');
                    localStorage.removeItem('gofit_user_profile');
                }
                this.updateHeader();
            })
            .catch(err => console.error('Auth sync failed:', err));
    },

    // Update header links based on login status
    updateHeader: function () {
        const isLoggedIn = localStorage.getItem('isLoggedIn');
        const profileStr = localStorage.getItem('gofit_user_profile');
        const loginBtns = document.querySelectorAll('.user-login');
        const logoutLinks = document.querySelectorAll('a[href="logout.php"]');

        let profileUrl = 'login.php';
        let loginTitle = 'Login';

        if (isLoggedIn && profileStr) {
            const profile = JSON.parse(profileStr);
            profileUrl = (profile.role === 'admin') ? 'admin.php' : 'customer.php';
            loginTitle = 'My Profile';

            logoutLinks.forEach(link => {
                link.style.display = 'inline-block';
                // Also update any text nodes that might say "Login" to "Logout"
                if (link.innerText.trim().toLowerCase() === 'login') {
                    link.innerText = 'Logout';
                }
            });
        } else {
            logoutLinks.forEach(link => link.style.display = 'none');
        }

        loginBtns.forEach(btn => {
            btn.href = profileUrl;
            btn.title = loginTitle;
            // If it's a text-based link (like in mobile menu)
            if (btn.innerText.trim().toLowerCase() === 'login' || btn.innerText.trim().toLowerCase() === 'profile') {
                btn.innerText = loginTitle;
            }
        });
    },

    // Handle logout
    logout: function () {
        localStorage.removeItem('isLoggedIn');
        localStorage.removeItem('gofit_user_profile');
        window.location.href = 'logout.php';
    }
};

// Auto-update header and sync with server on load
document.addEventListener('DOMContentLoaded', () => {
    Auth.updateHeader(); // Initial update with local data
    Auth.syncWithServer(); // Sync with server for accuracy
});
