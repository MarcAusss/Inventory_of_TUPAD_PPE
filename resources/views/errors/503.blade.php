<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>503 | SERVICE UNAVAILABLE </title>

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


.back-button {
    display: inline-block;
    padding: 8px 18px;
    border: 1.5px solid #1f4a9d;
    border-radius: 6px;
    background: #1f4a9d;
    color: #fff;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
}

.back-button:hover {
    background: #163a7d;
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
            <h3>503 | SERVICE UNAVAILABLE</h3>
            <p> The system is currently undergoing maintenance or experiencing high traffic.</p>
            <span>TUPAD | Tulong Panghanapbuhay sa ating Disadvantaged Workers</span>
           
             <a class="back-button" href="javascript:history.back()">Go Back</a>
<span class="system-name">
  
            
        </div>

    </div>

</body>

</html>
