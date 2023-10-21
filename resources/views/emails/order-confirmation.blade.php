<!-- resources/views/emails/order-confirmation.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
</head>
<body>
    <h1>Order Confirmation</h1>
    <p>Thank you for your order! Here are the order details:</p>
    <p><strong>Order ID:</strong> {{ $order->id }}</p>
    <p><strong>Book:</strong> {{ $order->name }}</p>
    <p><strong>Author:</strong> {{ $order->author }}</p>
    <p><strong>Description:</strong> {{ $order->description }}</p>

    <!-- Add more order details here -->
</body>
</html>
