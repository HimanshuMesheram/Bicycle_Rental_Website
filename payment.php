<?php
session_start();
error_reporting(0);
include('includes/config.php');

if(strlen($_SESSION['login'])==0) {  
    header('location:index.php');
} else {

    if(isset($_GET['amount']) && isset($_GET['bookingId'])) {
        $totalAmount = $_GET['amount'];
        $bookingId = $_GET['bookingId'];
    } else {
        header('location:book_bicycle.php');
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <!-- Add Stripe library -->
    <script src="https://js.stripe.com/v3/"></script>
</head>

<body>
    <h2>Payment Details</h2>
    <p>You need to pay <?php echo $totalAmount; ?> for your booking.</p>

    <form id="payment-form">
        <!-- Payment gateway form fields -->
        <label for="cardNumber">Card Number:</label>
        <div id="cardNumber"></div><br><br>

        <label for="expiryDate">Expiry Date:</label>
        <div id="expiry"></div><br><br>

        <label for="cvv">CVV:</label>
        <div id="cvv"></div><br><br>

        <input type="hidden" id="totalAmount" name="totalAmount" value="<?php echo $totalAmount; ?>">
        <input type="hidden" id="bookingId" name="bookingId" value="<?php echo $bookingId; ?>">

        <button id="card-button" type="submit">Pay Now</button>
    </form>

    <!-- JavaScript to handle Stripe -->
    <script>
        var stripe = Stripe('YOUR_STRIPE_PUBLIC_KEY');
        var elements = stripe.elements();

        var card = elements.create('card');
        card.mount('#cardNumber');

        var cardExpiry = elements.create('cardExpiry');
        cardExpiry.mount('#expiry');

        var cardCvc = elements.create('cardCvc');
        cardCvc.mount('#cvv');

        var form = document.getElementById('payment-form');

        form.addEventListener('submit', function(event) {
            event.preventDefault();

            var totalAmount = document.getElementById('totalAmount').value;
            var bookingId = document.getElementById('bookingId').value;

            stripe.createPaymentMethod({
                type: 'card',
                card: card
            }).then(function(result) {
                if (result.error) {
                    console.error(result.error.message);
                } else {
                    var paymentData = {
                        payment_method: result.paymentMethod.id,
                        totalAmount: totalAmount,
                        bookingId: bookingId
                    };

                    fetch('payment_process.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(paymentData)
                    }).then(function(response) {
                        return response.json();
                    }).then(function(data) {
                        console.log(data);
                        // Handle success or error response from payment process
                    }).catch(function(error) {
                        console.error('Error:', error);
                    });
                }
            });
        });
    </script>
</body>

</html>
