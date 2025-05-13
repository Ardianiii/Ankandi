   document.addEventListener('DOMContentLoaded', () => {
    const countdowns = {};
    const autoBidTimeouts = {};

    function getRandomDelay() {
        return Math.floor(Math.random() * 3 + 1) * 1000; // 1s to 3s
    }

    function startCountdown(productId) {
        const timerEl = document.getElementById(`countdown-${productId}`);
        let timeLeft = 15;

        if (!timerEl) return;

        clearInterval(countdowns[productId]);
        clearTimeout(autoBidTimeouts[productId]);

        // Randomly schedule auto-bid between 1-3s before timer ends
        const autoBidDelay = getRandomDelay(); // 1000–3000ms
        autoBidTimeouts[productId] = setTimeout(() => {
            checkAndTriggerAutoBid(productId);
        }, 15000 - autoBidDelay); // Schedule before countdown ends

        const interval = setInterval(() => {
            if (timeLeft <= 0) {
                clearInterval(interval);
                countdowns[productId] = null;
                return;
            }

            timerEl.textContent = timeLeft + 's';
            timerEl.style.color = timeLeft <= 5 ? 'red' : 'black';
            timeLeft--;
        }, 1000);

        countdowns[productId] = interval;
    }

    function handleBid(productId, isAuto = false) {
        const formData = new URLSearchParams();
        formData.append('product_id', productId);
        if (isAuto) formData.append('auto_bid', '1');

        fetch('bids/place_bid.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: formData.toString()
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (!isAuto) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'You are now the highest bidder!',
                        confirmButtonText: 'Continue Bidding'
                    });
                }

                const priceEl = document.querySelector(`#product-price-${productId}`);
                if (priceEl && data.new_price !== undefined) {
                    priceEl.textContent = '€' + data.new_price;
                }

                if (!isAuto && data.new_balance !== undefined) {
                    const balanceEl = document.querySelector('.text-white.me-3');
                    if (balanceEl) {
                        balanceEl.textContent = 'Bids Left: ' + data.new_balance;
                    }
                }

                const bidHistoryEl = document.querySelector(`#bid-history-${productId}`);
                if (bidHistoryEl && data.new_bidder && data.new_bid && data.timestamp) {
                    const newBidRow = document.createElement('tr');
                    newBidRow.innerHTML = `<td>${data.new_bidder}</td><td>€${data.new_bid}</td><td>${data.timestamp}</td>`;
                    bidHistoryEl.appendChild(newBidRow);
                }

                const lastBidderEl = document.querySelector(`#last-bidder-${productId}`);
                if (lastBidderEl && data.new_bidder) {
                    lastBidderEl.textContent = data.new_bidder;
                }

                startCountdown(productId); // Restart countdown after bid
            } else if (!isAuto) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || 'Something went wrong with your bid.'
                });
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            if (!isAuto) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Something went wrong with the bid request.'
                });
            }
        });
    }

    function checkAndTriggerAutoBid(productId) {
        fetch('bids/get_last_bidder.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'product_id=' + encodeURIComponent(productId)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                if (data.last_bidder !== "System") {
                    console.log(`Auto-bid triggered for product ${productId}`);
                    handleBid(productId, true);
                    setTimeout(() => {
                        updateBidderInfo(productId);
                    }, 1000);
                } else {
                    console.log(`Auto-bid skipped — last bid was already from System`);
                }
            }
        })
        .catch(err => console.error('Auto-bid check failed:', err));
    }

    function updateBidderInfo(productId) {
        fetch('bids/get_last_bidder.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'product_id=' + encodeURIComponent(productId)
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const lastBidderEl = document.querySelector(`#last-bidder-${productId}`);
                const priceEl = document.querySelector(`#product-price-${productId}`);
                const bidHistoryEl = document.querySelector(`#bid-history-${productId}`);

                if (lastBidderEl && data.last_bidder !== undefined) {
                    lastBidderEl.textContent = data.last_bidder;
                }

                if (priceEl && data.new_price !== undefined) {
                    priceEl.textContent = '€' + data.new_price;
                }

                if (bidHistoryEl && data.last_bidder && data.new_price && data.timestamp) {
                    const newBidRow = document.createElement('tr');
                    newBidRow.innerHTML = `<td>${data.last_bidder}</td><td>€${data.new_price}</td><td>${data.timestamp}</td>`;
                    if (bidHistoryEl.children.length >= 10) {
                        bidHistoryEl.removeChild(bidHistoryEl.firstChild);
                    }
                    bidHistoryEl.appendChild(newBidRow);
                }
            }
        })
        .catch(err => console.error('Update fetch error:', err));
    }

    // Handle manual bid buttons
    document.querySelectorAll('.bid-now-btn, .bid-btn, .place-bid-btn').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();
            const productId = button.dataset.productId || button.getAttribute('data-id');
            if (productId) handleBid(productId, false);
        });
    });

    // Initialize countdowns on page load
    document.querySelectorAll('[id^="countdown-"]').forEach(el => {
        const productId = el.id.replace('countdown-', '');
        startCountdown(productId);
    });

    // Periodic UI refresh
    document.querySelectorAll('.product').forEach(product => {
        const productId = product.dataset.productId;
        setInterval(() => updateBidderInfo(productId), 3000);
    });
});
