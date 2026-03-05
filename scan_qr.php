<?php require_once __DIR__ . '/db.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Scan QR | GymEdge</title>

    <!-- QR Scanner Library -->
    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        body {
            margin: 0;
            font-family: 'Poppins', sans-serif;
            background: radial-gradient(circle at top, #111, #000);
            color: #fff;
            text-align: center;
            padding: 40px;
        }

        h1 {
            color: #ffb700;
            margin-bottom: 30px;
        }

        #reader {
            width: 350px;
            margin: auto;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 0 25px rgba(255, 165, 0, 0.3);
        }

        #result {
            margin-top: 25px;
            font-size: 18px;
            font-weight: bold;
        }

        .success {
            color: #2aff2a;
            animation: glowGreen 1s infinite alternate;
        }

        .error {
            color: #ff4d4d;
        }

        @keyframes glowGreen {
            from {
                text-shadow: 0 0 5px #2aff2a;
            }

            to {
                text-shadow: 0 0 20px #2aff2a;
            }
        }

        button {
            margin-top: 30px;
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, #ffb700, #ffa500);
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <h1>📷 Scan Member QR</h1>

    <div id="reader"></div>
    <div id="result"></div>

    <button onclick="window.location.href='index.php'">Back</button>

    <script>
        let scanner;

        function onScanSuccess(decodedText) {

            // Stop scanner after first successful read
            scanner.clear();

            fetch("mark_attendance.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: "qr_value=" + encodeURIComponent(decodedText)
                })
                .then(response => response.text())
                .then(data => {
                    document.getElementById("result").innerHTML = data;

                    // Restart scanner after 3 seconds (optional)
                    setTimeout(() => {
                        startScanner();
                    }, 3000);
                });
        }

        function startScanner() {
            scanner = new Html5QrcodeScanner(
                "reader", {
                    fps: 10,
                    qrbox: 250
                }
            );
            scanner.render(onScanSuccess);
        }

        startScanner();
    </script>

</body>

</html>