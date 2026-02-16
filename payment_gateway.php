<?php
// payment_gateway.php
require_once 'db_connect.php';

$method = isset($_GET['method']) ? $_GET['method'] : 'card';
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';

// Simulation: If method is not set correctly, default to card
if (!in_array($method, ['card', 'fpx'])) {
    $method = 'card';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Payment Gateway | GoFit</title>
    <link href="https://fonts.googleapis.com/css?family=Muli:300,400,500,600,700,800,900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/bootstrap.min.css" type="text/css">
    <link rel="stylesheet" href="css/font-awesome.min.css" type="text/css">
    <style>
        body { background: #f4f7f6; font-family: 'Muli', sans-serif; height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; }
        .gateway-container { background: #fff; width: 100%; max-width: 450px; padding: 40px; border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border-top: 5px solid #f36100; }
        .header { text-align: center; margin-bottom: 30px; }
        .header img { height: 40px; margin-bottom: 10px; }
        .header h4 { font-weight: 700; color: #333; text-transform: uppercase; letter-spacing: 1px; }
        
        .method-branding { text-align: center; padding: 20px; background: #f9f9f9; border-radius: 4px; margin-bottom: 25px; }
        .method-branding img { height: 30px; }
        
        .form-group label { font-weight: 600; color: #555; font-size: 14px; }
        .form-control { height: 45px; border: 1px solid #ddd; border-radius: 4px; }
        
        .btn-pay { background: #f36100; color: #fff; width: 100%; height: 50px; font-weight: 700; border: none; border-radius: 4px; margin-top: 20px; transition: 0.3s; }
        .btn-pay:hover { background: #d45400; transform: translateY(-2px); }
        
        .footer-note { text-align: center; color: #888; font-size: 12px; margin-top: 25px; }
        .footer-note i { color: #28a745; margin-right: 5px; }
        
        #processing { display: none; text-align: center; }
        .spinner { width: 40px; height: 40px; border: 4px solid #f36100; border-top: 4px solid transparent; border-radius: 50%; animation: spin 0.8s linear infinite; margin: 0 auto 20px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>

<div class="gateway-container">
    <div id="payment-form">
        <div class="header">
            <h4>GoFit Secure Pay</h4>
        </div>

        <div class="method-branding">
            <?php if ($method === 'card'): ?>
                <img src="img/payment-visa.png" alt="Visa" style="margin-right: 15px;">
                <img src="img/payment-mastercard.png" alt="Mastercard">
                <p style="margin-top: 10px; font-size: 13px; color: #666;">Credit / Debit Card Payment</p>
            <?php else: ?>
                <img src="img/payment-fpx.png" alt="FPX">
                <p style="margin-top: 10px; font-size: 13px; color: #666;">FPX Online Banking</p>
            <?php endif; ?>
        </div>

        <?php if ($method === 'card'): ?>
            <div class="form-group mb-3">
                <label>Name on Card</label>
                <input type="text" id="card_name" class="form-control" placeholder="John Doe">
            </div>
            <div class="form-group mb-3">
                <label>Card Number</label>
                <input type="text" id="card_number" class="form-control" placeholder="0000 0000 0000 0000" maxlength="19" inputmode="numeric">
                <small id="card_err" style="color: #dc3545; display: none;">Invalid card number</small>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label>Expiry Date</label>
                    <input type="text" id="card_expiry" class="form-control" placeholder="MM/YY" maxlength="5" inputmode="numeric">
                </div>
                <div class="col-6 mb-3">
                    <label>CVV</label>
                    <input type="password" id="card_cvv" class="form-control" placeholder="***" maxlength="3" inputmode="numeric">
                </div>
            </div>
        <?php else: ?>
            <div class="form-group mb-3">
                <label>Select Bank</label>
                <select id="fpx_bank" class="form-control">
                    <option value="">-- Choose Bank --</option>
                    <option>Maybank2u</option>
                    <option>CIMB Clicks</option>
                    <option>Public Bank</option>
                    <option>RHB Now</option>
                    <option>AmBank</option>
                    <option>Bank Islam</option>
                </select>
            </div>
        <?php endif; ?>

        <button id="auth-btn" class="btn-pay" onclick="simulatePayment()">Authorize Payment</button>

        <div class="footer-note">
            <i class="fa fa-lock"></i> Secured with 256-bit SSL Encryption
        </div>
    </div>

    <div id="processing">
        <div class="spinner"></div>
        <h5 id="processing-title">Verifying Transaction...</h5>
        <p id="processing-text" style="color: #888; font-size: 14px;">Please do not close or refresh this page.</p>
    </div>
</div>

<script>
    // Card Formatting and Validation
    document.addEventListener('DOMContentLoaded', () => {
        const cardNumber = document.getElementById('card_number');
        const cardExpiry = document.getElementById('card_expiry');
        const cardCvv = document.getElementById('card_cvv');

        if (cardNumber) {
            cardNumber.addEventListener('input', (e) => {
                let val = e.target.value.replace(/\D/g, '');
                let formatted = val.match(/.{1,4}/g)?.join(' ') || '';
                e.target.value = formatted;
            });
        }

        if (cardExpiry) {
            cardExpiry.addEventListener('input', (e) => {
                let val = e.target.value.replace(/\D/g, '');
                if (val.length > 2) {
                    val = val.substring(0, 2) + '/' + val.substring(2, 4);
                }
                e.target.value = val;
            });
        }
    });

    function validateCard() {
        const name = document.getElementById('card_name').value.trim();
        const num = document.getElementById('card_number').value.replace(/\s/g, '');
        const exp = document.getElementById('card_expiry').value;
        const cvv = document.getElementById('card_cvv').value;

        // Simple validation rules
        if (name === "") {
            alert("Please enter the name on the card.");
            return false;
        }

        if (num.length !== 16) {
            alert("Card Number must be exactly 16 digits.");
            return false;
        }

        if (!/^\d{2}\/\d{2}$/.test(exp)) {
            alert("Expiry Date must be in MM/YY format.");
            return false;
        }

        const [m, y] = exp.split('/');
        const month = parseInt(m);
        const year = parseInt('20' + y);
        const now = new Date();
        const currentMonth = now.getMonth() + 1;
        const currentYear = now.getFullYear();

        if (month < 1 || month > 12) {
            alert("Invalid month in expiry date.");
            return false;
        }

        if (year < currentYear || (year === currentYear && month < currentMonth)) {
            alert("Card has expired.");
            return false;
        }

        if (cvv.length !== 3) {
            alert("CVV must be 3 digits.");
            return false;
        }

        return true;
    }

    function simulatePayment() {
        const method = '<?php echo $method; ?>';
        
        if (method === 'card') {
            if (!validateCard()) return;
        } else {
            if (document.getElementById('fpx_bank').value === "") {
                alert("Please select a bank.");
                return;
            }
        }

        document.getElementById('payment-form').style.display = 'none';
        document.getElementById('processing').style.display = 'block';

        // Retrieve order details from localStorage
        const lastOrder = JSON.parse(localStorage.getItem('gofit_last_order_pending'));
        if (!lastOrder) {
            alert("Order data lost. Please try again.");
            window.location.href = 'checkout.html';
            return;
        }

        // Prepare Payload
        const orderData = {
            user_id: lastOrder.user_id,
            items: lastOrder.items,
            total: lastOrder.total,
            payment_method: lastOrder.payment_method
        };

        // Artificial delay for realism
        setTimeout(() => {
            fetch('api/place_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(orderData)
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    // Update the final order object for success page
                    const finalOrder = {
                        ...lastOrder,
                        order_id: response.order_id,
                        date: new Date().toLocaleDateString()
                    };
                    localStorage.setItem('gofit_last_order', JSON.stringify(finalOrder));
                    localStorage.removeItem('gofit_last_order_pending');
                    
                    window.location.href = 'success.html?order_id=' + response.order_id;
                } else {
                    document.getElementById('payment-form').style.display = 'block';
                    document.getElementById('processing').style.display = 'none';
                    alert("Payment Failed: " + response.message);
                }
            })
            .catch(err => {
                document.getElementById('payment-form').style.display = 'block';
                document.getElementById('processing').style.display = 'none';
                alert("Network Error during payment processing.");
            });
        }, 2000);
    }
</script>

</body>
</html>
