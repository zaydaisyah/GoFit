/* Profile Persistence Logic for GoFit */

const UserProfile = {
    // Key for localStorage
    STORAGE_KEY: 'gofit_user_profile',

    // Default Data
    defaultData: {
        name: 'John Doe',
        email: 'john.doe@example.com',
        phone: '012-3456789',
        avatar: 'img/hero/hero-2.jpg',
        // Gamification Data
        points: 2450, // Starting points
        streak: 5,
        history: [], // { date: 'YYYY-MM-DD', action: 'Activity', points: 10 }
        joinedChallenges: [], // Store IDs of joined challenges
        completedChallenges: [] // Store IDs of completed challenges
    },

    // Load data from storage or Server
    loadProfile: function () {
        // HYBRID: Check if Server Data injected by PHP exists
        if (window.serverData && window.serverData.user) {
            console.log("Loading from Server Data:", window.serverData);
            const sUser = window.serverData.user;

            // Map PHP User to JS Object
            const data = {
                ...this.defaultData,
                name: sUser.name,
                email: sUser.email,
                avatar: sUser.avatar,
                points: parseInt(sUser.points),
                tier: sUser.tier,
                streak: parseInt(sUser.streak),
                id: sUser.id // Important for API calls
            };

            // SYNC: Update LocalStorage with latest Server Data so other functions work
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));

            // Init Rewards from Server
            if (window.serverData.rewards) {
                this.rewards = window.serverData.rewards; // Override hardcoded rewards
            }

            // Init Leaderboard from Server
            if (window.serverData.leaderboard) {
                this.leaderboard = window.serverData.leaderboard;
            }

            this.renderProfile(data);
            this.renderGamification(data);
            this.renderRewards(false);

            // If we have leaderboard data and the modal is open/or we want to pre-render
            // this.renderLeaderboard(data); 

        } else {
            // Fallback to LocalStorage (Static HTML mode)
            const stored = localStorage.getItem(this.STORAGE_KEY);
            const data = stored ? { ...this.defaultData, ...JSON.parse(stored) } : this.defaultData;

            if (!data.history) data.history = [];
            if (!data.joinedChallenges) data.joinedChallenges = [];
            if (!data.completedChallenges) data.completedChallenges = [];

            this.renderProfile(data);
            this.renderGamification(data);
            this.renderRewards(false);
        }
    },

    // Save data to storage
    saveProfile: function () {
        const name = document.getElementById('userName').value;
        const email = document.getElementById('userEmail').value;
        const phone = document.getElementById('userPhone').value;
        const avatar = document.getElementById('profileImage').src;

        // Get current existing data to preserve points/history
        const currentData = this.getStoredData();

        const data = {
            ...currentData,
            name: name,
            email: email,
            phone: phone,
            avatar: avatar
        };

        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
        alert('Profile saved successfully!');
    },

    // Helper to get raw data
    getStoredData: function () {
        const stored = localStorage.getItem(this.STORAGE_KEY);
        const data = stored ? { ...this.defaultData, ...JSON.parse(stored) } : this.defaultData;
        if (!data.joinedChallenges) data.joinedChallenges = [];
        if (!data.completedChallenges) data.completedChallenges = [];
        return data;
    },

    // Handle Challenge Actions: Join -> Complete
    handleChallengeAction: function (id, points, btnElement) {
        const data = this.getStoredData();
        const joinedIndex = data.joinedChallenges.indexOf(id);
        const isCompleted = data.completedChallenges.includes(id);

        if (isCompleted) {
            // Already done, do nothing
            return;
        }

        if (joinedIndex === -1) {
            // Step 1: Join
            data.joinedChallenges.push(id);
            alert("Challenge Accepted! Good luck.");
        } else {
            // Step 2: Complete
            data.completedChallenges.push(id);
            // Remove from joined list (optional, but cleaner if we consider 'joined' as 'active')
            // data.joinedChallenges.splice(joinedIndex, 1); 

            // Add Points
            this.addPointsInternal(data, `Challenge Completed`, points);
            alert(`Challenge Completed! You earned ${points} Points.`);
        }

        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
        this.renderChallenges(); // Re-render UI
        this.renderGamification(data); // Update header points if visible
    },

    // Internal helper to modify data object directly
    addPointsInternal: function (data, action, points) {
        const date = new Date().toISOString().split('T')[0];
        data.points += points;
        data.history.unshift({ date: date, action: action, points: points });
        data.streak += 1; // Simple streak increment
    },

    // Public wrapper for ad-hoc point items (simulations etc)
    addPoints: function (action, points) {
        const data = this.getStoredData();

        // HYBRID: Use API if ID exists
        if (data.id) {
            fetch('api/log_activity.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: data.id, action: action, points: points })
            })
                .then(res => res.json())
                .then(response => {
                    if (response.success) {
                        // Update Local State with Server Response
                        data.points = response.new_points;
                        this.addPointsInternal(data, action, points); // Update history/streak locally for instant feedback
                        localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
                        this.renderGamification(data);
                        alert(`Awesome! You earned ${points} PTS for ${action}. (Saved to Database)`);
                    } else {
                        alert("Error saving activity: " + response.message);
                    }
                })
                .catch(err => console.error("API Error:", err));
        } else {
            // Fallback for offline/static
            this.addPointsInternal(data, action, points);
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
            this.renderGamification(data);
            alert(`Awesome! You earned ${points} PTS for ${action}. (Offline Mode)`);
        }
    },

    // Render Basic Profile Data
    renderProfile: function (data) {
        const nameEl = document.querySelector('.dashboard-sidebar h3');
        const rankEl = document.querySelector('.dashboard-sidebar p');
        const emailEl = document.querySelector('.dashboard-sidebar .info-list div:nth-child(1) div');
        const phoneEl = document.querySelector('.dashboard-sidebar .info-list div:nth-child(2) div');
        const imgEl = document.getElementById('profileImage');
        const memberSinceEl = document.getElementById('member-since');

        if (nameEl) nameEl.textContent = data.name;
        // Calculate dynamic rank for sidebar
        const currentRank = this.calculateRank(data.points);
        if (rankEl) {
            // rankEl is now the p#sidebar-rank-display or similar
            // We inject image + text
            rankEl.innerHTML = `<img src="${currentRank.icon}" style="width: 20px; vertical-align: middle; margin-right: 5px;"> ${currentRank.rank} Rank`;
        } else {
            // Fallback if old HTML
            const oldRankEl = document.querySelector('.dashboard-sidebar p');
            if (oldRankEl) oldRankEl.textContent = currentRank.rank + ' Rank';
        }

        if (emailEl) emailEl.textContent = data.email;
        if (phoneEl) phoneEl.textContent = data.phone;
        if (imgEl && data.avatar) imgEl.src = data.avatar;

        if (memberSinceEl) {
            // Mock Member Since or store it. For now, static or random logic if not in data.
            // Let's assume everyone joined Jan 2024 for simplicity, or save it if we had a backend.
            memberSinceEl.textContent = "Jan 2024";
        }

        const greetingEl = document.getElementById('profile-greeting');
        if (greetingEl) {
            greetingEl.innerHTML = `${this.getGreeting()}, <span style="color:#f36100;">${data.name.split(' ')[0]}</span>!`;
        }
    },

    // Helper for Rank Calculation
    // Helper for Rank Calculation
    calculateRank: function (points) {
        if (points >= 3000) return { rank: 'Warrior', icon: 'img/badges/warrior.png', next: 'Max', target: 3000 };
        if (points >= 1500) return { rank: 'Elite', icon: 'img/badges/elite.png', next: 'Warrior', target: 3000 };
        if (points >= 500) return { rank: 'Fighter', icon: 'img/badges/fighter.png', next: 'Elite', target: 1500 };
        return { rank: 'Rookie', icon: 'img/badges/rookie.png', next: 'Fighter', target: 500 };
    },

    getGreeting: function () {
        const hour = new Date().getHours();
        let greeting = 'Hello';
        if (hour < 12) greeting = 'Good Morning';
        else if (hour < 18) greeting = 'Good Afternoon';
        else greeting = 'Good Evening';
        return greeting;
    },

    getRecommendations: function (points) {
        if (points < 400) return { title: "Start Strong!", text: "Try our 'Intro to Yoga' class to build a solid foundation.", link: "classes-booking.html" };
        if (points < 800) return { title: "Step It Up!", text: "Ready for a challenge? Book a HIIT session to boost your cardio.", link: "timetable.html" };
        if (points < 1200) return { title: "Pro Level!", text: "Maintain your dominance with Advanced Strength Training.", link: "fitness-plans.html" };
        return { title: "Legendary Status!", text: "You're a pro! Why not try a Master Class or mentor a newbie?", link: "#" };
    },

    checkNotifications: function (data) {
        const notifs = [];
        if (data.streak >= 3) {
            notifs.push({ type: 'success', text: `🔥 You're on fire! ${data.streak} day streak. Keep it up!` });
        }

        // Rank Nudge
        const rankInfo = this.calculateRank(data.points);
        if (rankInfo.next !== 'Max') {
            const diff = rankInfo.target - data.points;
            if (diff > 0 && diff <= 150) {
                notifs.push({ type: 'warning', text: `🚀 Almost there! Only ${diff} pts to reach ${rankInfo.next}!` });
            }
        }
        return notifs;
    },

    // Help to animate numbers
    animateValue: function (obj, start, end, duration) {
        if (!obj) return;
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            const value = Math.floor(progress * (end - start) + start);
            obj.innerHTML = `${value} <span style="font-size:20px; color:#f36100;">PTS</span>`;
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    },

    // Redeem Reward Logic
    redeemReward: function (id, rewardName, cost, btnElement) {
        // HYBRID: API Call if server data exists
        if (window.serverData && window.serverData.user) {
            const data = this.getStoredData(); // Get current local state

            if (data.points < cost) {
                alert(`Not enough points! You need ${cost - data.points} more points to redeem ${rewardName}.`);
                return;
            }

            if (confirm(`Redeem ${rewardName} for ${cost} Points?`)) {
                // Disable button
                if (btnElement) {
                    btnElement.disabled = true;
                    btnElement.innerText = "Processing...";
                }

                // API Call
                fetch('api/redeem_reward.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ user_id: data.id, reward_id: id })
                })
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            alert(`Success! You have redeemed ${rewardName}.\nYour Coupon Code: ${res.coupon}\n(Show this at the counter)`);

                            // Update Local State with new points from server
                            data.points = res.new_points;
                            // Add history item manually to local state for immediate feedback
                            if (!data.history) data.history = [];
                            data.history.push({
                                date: new Date().toISOString().split('T')[0],
                                action: `Redeemed: ${rewardName}`,
                                points: -cost
                            });

                            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
                            this.renderGamification(data);

                            // Button feedback
                            if (btnElement) {
                                btnElement.innerText = "Redeemed!";
                                btnElement.style.background = "#333";
                                setTimeout(() => {
                                    btnElement.innerText = "Redeem";
                                    btnElement.disabled = false;
                                    btnElement.style.background = "";
                                }, 3000);
                            }
                        } else {
                            alert("Redemption Failed: " + res.message);
                            if (btnElement) {
                                btnElement.disabled = false;
                                btnElement.innerText = "Redeem";
                            }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert("Error processing redemption. Please try again.");
                        if (btnElement) {
                            btnElement.disabled = false;
                            btnElement.innerText = "Redeem";
                        }
                    });
            }
            return;
        }

        // LEGACY / OFFLINE MODE
        const data = this.getStoredData();

        if (data.points < cost) {
            alert(`Not enough points! You need ${cost - data.points} more points to redeem ${rewardName}.`);
            return;
        }

        if (confirm(`Redeem ${rewardName} for ${cost} Points?`)) {
            // Deduct Points
            this.addPointsInternal(data, `Redeemed: ${rewardName}`, -cost);

            // Generate Mock Coupon
            const coupon = 'GO-' + Math.random().toString(36).substr(2, 6).toUpperCase();
            alert(`Success! You have redeemed ${rewardName}.\nYour Coupon Code: ${coupon}\n(Show this at the counter)`);

            // Save and Update UI
            localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
            this.renderGamification(data);

            // Visual feedback on button
            if (btnElement) {
                const originalText = btnElement.innerText;
                btnElement.innerText = "Redeemed!";
                btnElement.disabled = true;
                btnElement.style.background = "#333";
                setTimeout(() => {
                    btnElement.innerText = originalText;
                    btnElement.disabled = false;
                    btnElement.style.background = ""; // Reset
                }, 3000);
            }
        }
    },

    // Render Gamification to DOM
    renderGamification: function (data) {
        // Greeting & Header Points
        const greetingEl = document.getElementById('profile-greeting');
        if (greetingEl) greetingEl.innerHTML = `${this.getGreeting()}, <span style="color:#f36100">${data.name.split(' ')[0]}</span>!`;

        const pointsDisplay = document.querySelector('.points-display');
        if (pointsDisplay) {
            // Check if we are already displaying this value to avoid re-animating on load if not needed
            // But for "moving" effect on update, we want to animate from distinct previous value.
            // Since we reload data often, simpler to just animate from 0 or close.
            // Better: Animate from 0 to current on page load, OR store 'lastPoints' in DOM/memory?
            // For now: Animate from 0 to data.points on load gives a nice effect.
            // On update, we might want to capture current value. 
            // Let's try parsing current value.
            let currentVal = 0;
            // Try to parse existing number
            const text = pointsDisplay.innerText || "0";
            const match = text.match(/\d+/);
            if (match) currentVal = parseInt(match[0]);

            // If huge jump (like page load from 0), animate. If small jump (add points), animate.
            // If equal, don't animate to avoid flicker.
            if (currentVal !== data.points) {
                this.animateValue(pointsDisplay, currentVal, data.points, 1500);
            }
        }

        const streakDisplay = document.getElementById('streak-display');
        if (streakDisplay) streakDisplay.innerText = data.streak;

        this.updateRankUI(data.points);
        this.renderNotifications(this.checkNotifications(data));

        const historyContainer = document.getElementById('points-timeline-body');
        if (historyContainer) {
            historyContainer.innerHTML = '';
            if (data.history.length === 0) {
                historyContainer.innerHTML = '<div class="text-center" style="color: #666;">No recent activity.</div>';
            } else {
                data.history.slice(0, 5).forEach(item => {
                    const row = `
                    <div class="timeline-item">
                        <div class="timeline-date">${item.date}</div>
                        <div class="timeline-content">
                            ${item.action} <span class="timeline-points pull-right">+${item.points} PTS</span>
                        </div>
                    </div>`;
                    historyContainer.innerHTML += row;
                });
            }
        }

        this.renderChallenges();
        this.renderLeaderboard(data);
    },

    renderNotifications: function (notifs) {
        const notifContainer = document.getElementById('notification-area');
        if (!notifContainer) return;
        notifContainer.innerHTML = '';
        notifs.forEach(n => {
            const div = document.createElement('div');
            div.className = `alert alert-${n.type === 'error' ? 'danger' : (n.type === 'warning' ? 'warning' : 'success')}`;
            div.style.marginBottom = '10px';
            div.style.fontSize = '14px';
            div.innerHTML = n.text;
            notifContainer.appendChild(div);
        });
    },

    leaderboard: [
        { name: "Sarah Connor", points: 5200, avatar: "img/hero/hero-1.jpg" },
        { name: "Mike Tyson", points: 4800, avatar: "img/hero/hero-2.jpg" },
        { name: "Bruce Lee", points: 4500, avatar: "img/hero/hero-3.jpg" },
        { name: "Lara Croft", points: 4100, avatar: "img/hero/hero-1.jpg" },
        { name: "Rocky Balboa", points: 3900, avatar: "img/hero/hero-2.jpg" },
        { name: "Wonder Woman", points: 3600, avatar: "img/hero/hero-3.jpg" },
        { name: "Thor Odinson", points: 3200, avatar: "img/hero/hero-1.jpg" },
        { name: "Hulk Hogan", points: 3000, avatar: "img/hero/hero-2.jpg" },
        { name: "Black Widow", points: 2800, avatar: "img/hero/hero-3.jpg" },
        { name: "Captain America", points: 2500, avatar: "img/hero/hero-1.jpg" }
    ],

    renderLeaderboard: function (currentUserData) {
        const tbody = document.querySelector('#leaderboard-table tbody');
        if (!tbody) return;

        // 1. Combine mock data with current user
        let allUsers = [...this.leaderboard];

        // Check if user is already in list (by name collision) or add them
        // For simulation, we'll force add/update the current user entry
        const userEntry = {
            name: currentUserData.name,
            points: currentUserData.points,
            avatar: currentUserData.avatar,
            isMe: true
        };

        allUsers.push(userEntry);

        // 2. Sort by Points Descending
        allUsers.sort((a, b) => b.points - a.points);

        // 3. Render
        tbody.innerHTML = '';
        allUsers.forEach((u, index) => {
            const rank = index + 1;
            const isMe = u.isMe ? 'style="background: rgba(243, 97, 0, 0.2); border-left: 3px solid #f36100;"' : '';
            const rankBadge = rank <= 3 ? `<i class="fa fa-trophy" style="color:${rank === 1 ? '#FFD700' : (rank === 2 ? '#C0C0C0' : '#CD7F32')}"></i>` : rank;

            // Tier based on points
            const tier = this.calculateRank(u.points).rank;

            const row = `
                <tr ${isMe}>
                    <td class="text-center" style="vertical-align: middle; color: #fff; font-weight: bold; border-top-color: rgba(255,255,255,0.05);">${rankBadge}</td>
                    <td style="vertical-align: middle; border-top-color: rgba(255,255,255,0.05);">
                        <img src="${u.avatar}" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 10px; border: 1px solid #555;">
                        <span style="color: #ccc; font-size: 14px;">${u.name} ${u.isMe ? '<span class="badge badge-warning" style="font-size: 9px; vertical-align: middle;">YOU</span>' : ''}</span>
                    </td>
                    <td style="vertical-align: middle; color: #aaa; font-size: 13px; border-top-color: rgba(255,255,255,0.05);">${tier}</td>
                    <td class="text-right" style="vertical-align: middle; color: #f36100; font-weight: bold; border-top-color: rgba(255,255,255,0.05);">${u.points}</td>
                </tr>
            `;
            tbody.innerHTML += row;

            // Update user's rank display on dashboard if it matches
            if (u.isMe) {
                const lbRankEl = document.getElementById('leaderboard-rank');
                if (lbRankEl) {
                    lbRankEl.innerText = "#" + (rank < 10 ? "0" + rank : rank);
                }
            }
        });
    },

    // Updated Challenges Data with Points
    challenges: [
        { id: 1, title: 'Daily Step Challenge', goal: 'Walk 8k-10k steps/day', reward: '500 PTS', points: 500, icon: 'fa-paw' },
        { id: 2, title: '7-Day Workout Streak', goal: 'Workout daily for 7 days', reward: '1000 PTS', points: 1000, icon: 'fa-fire' },
        { id: 3, title: 'Monthly Weight Loss', goal: 'Lose healthy weight in 1 month', reward: '1500 PTS', points: 1500, icon: 'fa-balance-scale' },
        { id: 4, title: 'Calories Burn', goal: 'Burn target calories/week', reward: '300 PTS', points: 300, icon: 'fa-fire-extinguisher' },
        { id: 5, title: 'Class Attendance', goal: 'Attend 10 classes/month', reward: '800 PTS', points: 800, icon: 'fa-users' },
        { id: 6, title: 'Beginner Fitness', goal: 'Complete 5 beginner workouts', reward: '400 PTS', points: 400, icon: 'fa-child' },
        { id: 7, title: 'Strength Training', goal: '3-4 strength workouts/week', reward: '600 PTS', points: 600, icon: 'fa-dumbbell' },
        { id: 8, title: 'Team Fitness', goal: 'Accumulate points together', reward: '2000 PTS', points: 2000, icon: 'fa-users', isTeam: true },
        { id: 9, title: 'Cardio Endurance', goal: '20km run/cycle/swim in month', reward: '1200 PTS', points: 1200, icon: 'fa-heartbeat' },
    ],

    // Huge List of Rewards
    rewards: [
        { id: 1, title: 'Free Protein Shake', desc: 'Redeem for a post-workout fuel up.', cost: 500, icon: 'fa-coffee' },
        { id: 2, title: '10% Off Merch', desc: 'Get a discount on your next gear purchase.', cost: 1000, icon: 'fa-shopping-bag' },
        { id: 3, title: 'Guest Pass', desc: 'Bring a friend to train with you for a day.', cost: 1500, icon: 'fa-users' },
        { id: 4, title: 'Personal Training', desc: 'One free session with a pro trainer.', cost: 5000, icon: 'fa-bolt' },
        { id: 5, title: 'GoFit Water Bottle', desc: 'Stay hydrated with a premium bottle.', cost: 2000, icon: 'fa-tint' },
        { id: 6, title: 'Yoga Mat', desc: 'High quality non-slip yoga mat.', cost: 3500, icon: 'fa-leaf' },
        { id: 7, title: 'Gym Towel', desc: 'Microfiber towel for sweat sessions.', cost: 800, icon: 'fa-square' },
        { id: 8, title: '1 Month Membership', desc: 'Get a free month of access.', cost: 12000, icon: 'fa-id-card' },
        { id: 9, title: 'Massage Session', desc: '30-minute recovery massage.', cost: 6000, icon: 'fa-magic' },
        { id: 10, title: 'Nutrition Plan', desc: 'Customized diet plan by experts.', cost: 4000, icon: 'fa-cutlery' },
        { id: 11, title: 'Hoodie', desc: 'Comfortable GoFit branded hoodie.', cost: 4500, icon: 'fa-shirtsinbulk' },
        { id: 12, title: 'Supplements Pack', desc: 'Starter pack of vitamins/protein.', cost: 5500, icon: 'fa-medkit' },
        { id: 13, title: 'Locker Rental', desc: 'Free locker rental for a month.', cost: 1500, icon: 'fa-lock' },
        { id: 14, title: 'Smart Band', desc: 'Track your fitness stats.', cost: 15000, icon: 'fa-clock-o' },
        { id: 15, title: 'VIP Workshop', desc: 'Access to exclusive fitness workshop.', cost: 3000, icon: 'fa-star' }
    ],

    isRewardsExpanded: false,

    toggleRewardsView: function () {
        this.isRewardsExpanded = !this.isRewardsExpanded;
        const btn = document.getElementById('view-all-rewards-btn');
        if (btn) {
            btn.innerHTML = this.isRewardsExpanded ? 'Show Less <i class="fa fa-arrow-up"></i>' : 'View All Rewards <i class="fa fa-arrow-right"></i>';
        }
        this.renderRewards(this.isRewardsExpanded);
    },

    renderRewards: function (showAll) {
        const container = document.getElementById('rewards-container');
        if (!container) return;

        container.innerHTML = '';

        // Decide how many to show
        const itemsToShow = showAll ? this.rewards : this.rewards.slice(0, 4);

        itemsToShow.forEach(r => {
            const card = `
                <div class="col-md-6 mb-3 fade-in-up">
                    <div class="reward-card">
                        <div class="reward-icon">
                            <i class="fa ${r.icon}"></i>
                        </div>
                        <div class="reward-details">
                            <h5>${r.title}</h5>
                            <p>${r.desc}</p>
                        </div>
                        <div class="reward-action">
                            <span class="coin-cost"><i class="fa fa-database"></i> ${r.cost}</span>
                            <button class="btn-redeem"
                                onclick="UserProfile.redeemReward(${r.id}, '${r.title}', ${r.cost}, this)">Redeem</button>
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += card;
        });
    },

    renderChallenges: function () {
        const container = document.getElementById('challenges-container');
        if (!container) return;

        const data = this.getStoredData(); // Get fresh data
        container.innerHTML = '';

        this.challenges.forEach(c => {
            const isJoined = data.joinedChallenges && data.joinedChallenges.includes(c.id);
            const isCompleted = data.completedChallenges && data.completedChallenges.includes(c.id);

            const isTeam = c.isTeam ? 'border: 2px solid #f36100;' : '';
            const teamBadge = c.isTeam ? '<span class="badge badge-warning" style="position:absolute; top:-10px; right:-10px; background:#f36100; color:#fff;">TEAM GOAL</span>' : '';

            // Determine Button State
            let btnText = "Join Challenge";
            let btnColor = "#f36100";
            let btnDisabled = "";
            let btnAction = `UserProfile.handleChallengeAction(${c.id}, ${c.points}, this)`;

            if (isCompleted) {
                btnText = "Completed";
                btnColor = "#333";
                btnDisabled = "disabled style='cursor:not-allowed; opacity:0.6; width:100%; padding:5px 0; font-size:12px; background:#333; border:none; color:#888;'";
            } else if (isJoined) {
                btnText = `Complete (+${c.points} PTS)`;
                btnColor = "#28a745"; // Green for action
            }

            // Define button HTML based on state
            let buttonHtml = `<button class="primary-btn btn-normal" onclick="${btnAction}" style="width:100%; padding:5px 0; font-size:12px; background:${btnColor}; border:none;">${btnText}</button>`;

            if (isCompleted) {
                buttonHtml = `<button class="primary-btn btn-normal" disabled style="width:100%; padding:5px 0; font-size:12px; background:#333; border:none; color:#888; cursor:not-allowed;">Completed <i class="fa fa-check"></i></button>`;
            }

            const card = `
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="challenge-card" style="position:relative; background:#1e1e1e; padding:20px; border-radius:10px; height:100%; ${isTeam}">
                        ${teamBadge}
                        <div class="icon-circle" style="width:50px; height:50px; background:#333; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:15px; color:#f36100;">
                            <i class="fa ${c.icon}" style="font-size:24px;"></i>
                        </div>
                        <h5 style="color:#fff; font-size:16px; margin-bottom:10px;">${c.title}</h5>
                        <p style="color:#aaa; font-size:13px; min-height:40px;">${c.goal}</p>
                        <div style="margin-top:15px; border-top:1px solid #333; padding-top:10px;">
                             <small style="color:#f36100; display:block; margin-bottom:10px;"><i class="fa fa-trophy"></i> Reward: ${c.reward}</small>
                             ${buttonHtml}
                        </div>
                    </div>
                </div>
            `;
            container.innerHTML += card;
        });
    },

    updateRankUI: function (points) {
        const rankInfo = this.calculateRank(points);

        const rankBadge = document.querySelector('.rank-badge');
        const rankImg = document.querySelector('.rank-card img');
        const userTierBadge = document.getElementById('user-tier-badge');

        if (rankBadge) rankBadge.innerText = rankInfo.rank;
        if (rankImg) rankImg.src = rankInfo.icon;
        if (userTierBadge) userTierBadge.innerText = rankInfo.rank;

        // Recommendation
        const recData = this.getRecommendations(points);
        const recContainer = document.getElementById('recommendation-card');
        if (recContainer) {
            recContainer.innerHTML = `
                <h5 style="color:#f36100; margin-bottom:5px;"><i class="fa fa-star"></i> ${recData.title}</h5>
                <p style="color:#ccc; font-size:14px; margin-bottom:10px;">${recData.text}</p>
                <a href="fitness_plans.html" class="primary-btn btn-normal" style="padding: 5px 15px; font-size: 12px;">Go Now</a>
            `;
        }

        // Progress Bar
        const progressFill = document.getElementById('points-progress-bar');
        const progressText = document.getElementById('next-rank-text');

        if (progressFill && rankInfo.target > 0) {
            const percentage = Math.min(100, Math.max(0, (points / rankInfo.target) * 100));
            progressFill.style.width = percentage + '%';
        }

        if (progressText) {
            if (rankInfo.next === 'Max') {
                progressText.innerHTML = `<span style="color: #f36100;">Max Rank Achieved!</span>`;
            } else {
                const diff = rankInfo.target - points;
                progressText.innerHTML = `Next Rank: <span style="color: #f36100;">${Math.max(0, diff)} PTS</span> away`;
            }
        }

        // Leaderboard Rank Calculation (Mock)
        // Simulate based on points: higher points = lower numeric rank
        let lbRank = 999;
        if (points > 0) lbRank = Math.floor(10000 / (points + 100));
        if (lbRank < 1) lbRank = 1;

        const lbRankEl = document.getElementById('leaderboard-rank');
        if (lbRankEl) {
            lbRankEl.innerText = "#" + (lbRank < 10 ? "0" + lbRank : lbRank);
        }

        // Update Rank Text in Banner
        const bannerRankText = document.querySelector('.rank-badge-text');
        if (bannerRankText) bannerRankText.innerText = rankInfo.rank;
    },

    // Handle Image upload and convert to Base64
    handleImageUpload: function (event) {
        const file = event.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                // Update image source immediately for preview
                document.getElementById('profileImage').src = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    },

    // Initialize
    init: function () {
        this.loadProfile();

        // --- Event Listeners ---

        // 1. Edit Profile Button (Opens Modal)
        const editBtn = document.getElementById('editProfileBtn');
        if (editBtn) {
            editBtn.addEventListener('click', () => {
                const data = this.getStoredData();
                // Pre-fill Modal
                document.getElementById('modalUserName').value = data.name;
                document.getElementById('modalUserEmail').value = data.email;
                document.getElementById('modalUserPhone').value = data.phone;
                document.getElementById('modalAvatarUrl').value = data.avatar;

                // Show Modal (Bootstrap 4)
                $('#editProfileModal').modal('show');
            });
        }

        // 2. Save Changes Button (Inside Modal)
        const saveBtn = document.getElementById('modalSaveBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                this.saveProfileFromModal();
            });
        }

        // 3. Avatar Upload Trigger (Simulate click on hidden input)
        const avatarLabel = document.querySelector('.avatar-upload-btn');
        if (avatarLabel) {
            avatarLabel.addEventListener('click', (e) => {
                // e.preventDefault(); // allow default label behavior
            });
        }

        // 4. File Input Change (Update Avatar Preview)
        const fileInput = document.getElementById('avatarUpload');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function (event) {
                        // Update UI immediately
                        document.getElementById('profileImage').src = event.target.result;
                        // Save to storage (as data URL)
                        UserProfile.updateAvatar(event.target.result);
                    };
                    reader.readAsDataURL(file);
                }
            });
        }

        // 5. Simulation Buttons (Demo Only)
        document.getElementById('btn-sim-workout')?.addEventListener('click', () => {
            this.addPoints('Workout Completed', 50);
            this.showNotification('Great job! 50 Points added.');
        });
        document.getElementById('btn-sim-class')?.addEventListener('click', () => {
            this.addPoints('Class Attended', 100);
            this.showNotification('Class checked in! 100 Points added.');
        });
        document.getElementById('btn-sim-bonus')?.addEventListener('click', () => {
            this.addPoints('Daily Bonus', 20);
            this.showNotification('Bonus collected! 20 Points added.');
        });
    },

    // Save Profile from Modal
    saveProfileFromModal: function () {
        const name = document.getElementById('modalUserName').value;
        const email = document.getElementById('modalUserEmail').value;
        const phone = document.getElementById('modalUserPhone').value;
        const avatar = document.getElementById('modalAvatarUrl').value; // Optional manual override

        const data = this.getStoredData();

        // Update fields
        data.name = name;
        data.email = email;
        data.phone = phone;
        if (avatar && avatar.trim() !== "") {
            data.avatar = avatar;
        }

        // Server-Side Update
        fetch('api/update_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: name,
                email: email,
                phone: phone, // Pass through, though not saved in DB yet
                avatar: (avatar && avatar.trim() !== "") ? avatar : null
            })
        })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    // Update Local Data
                    data.name = name;
                    data.email = email;
                    data.phone = phone;
                    if (response.new_avatar) {
                        data.avatar = response.new_avatar;
                    } else if (avatar && avatar.trim() !== "") {
                        // Fallback if server didn't return new avatar but saying success
                        data.avatar = avatar;
                    }

                    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
                    this.renderProfile(data);

                    $('#editProfileModal').modal('hide');
                    this.showNotification('Profile updated successfully!');
                } else {
                    alert("Update failed: " + response.message);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error connecting to server.");
            });
    },

    // Helper to update just the avatar
    updateAvatar: function (base64Image) {
        const data = this.getStoredData();

        // Optimistic UI Update (Preview)
        // document.getElementById('profileImage').src = base64Image;

        // Server Update
        fetch('api/update_profile.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name: data.name,
                email: data.email,
                avatar: base64Image
            })
        })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    if (response.new_avatar) {
                        data.avatar = response.new_avatar;
                    } else {
                        data.avatar = base64Image;
                    }
                    localStorage.setItem(this.STORAGE_KEY, JSON.stringify(data));
                    this.renderProfile(data); // Ensures all places update
                    this.showNotification('Avatar updated!');
                } else {
                    alert("Avatar upload failed: " + response.message);
                    // Revert on failure?
                    // this.renderProfile(data); 
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error uploading avatar.");
            });
    },

    // Helper to show notification
    showNotification: function (msg) {
        // Simple alert for now, or append to notification area
        alert(msg);
    }
};

// Start when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    UserProfile.init();
});
