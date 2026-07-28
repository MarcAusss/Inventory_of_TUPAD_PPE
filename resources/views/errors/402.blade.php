
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>402| PAYMENT REQUIRED </title>

    <style>
       * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    font-family: 'Roboto', sans-serif;
}

.wrap {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.error-icon {
    margin-bottom: 10px;
}

.error-icon img {
    width: 450px;
    height: auto;
    display: block;
}

.content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    text-align: center;
}


.content h3 {
    border-bottom: 3px solid yellow;
    font-size: 24px;
    font-weight: bold;
}

.content p {
    font-size: 20px;
    font-weight: 600;
}


.content span {
    font-size: 10px;
    color: #666;
    font-weight: 500;
}
.access-denied {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    padding: 6px 16px;
    margin: 4px 0;

    border: 1.5px solid #1f4a9d;
    border-radius: 25px;

    background: #fff;
}

.access-denied .lock {
    font-size: 18px;
    color: #f4b400;
}

.access-denied .text {
    font-size: 14px;
    font-weight: 600;
    color: #b22222;
    letter-spacing: 1px;
}

.system-name {
    font-size: 10px;
    color: #666;
}
    </style>
</head>

<body>

    <div class="wrap">

        <div class="error-icon">
           <img src="/images/errorlogo.png" alt="ERROR LOGO">
        </div>

        <div class="content">
            <h3>402| PAYMENT REQUIRED </h3>
            <p>This feature requires payment or an active subscription.</p>

</div>
<span class="system-name">
            <span>TUPAD | Tulong Panghanapbuhay sa ating Disadvantaged Workers</span>
        </div>

    </div>

</body>

</html>
