<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR to PDF Scanner</title>
    <script src="js/qr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; margin: 0; background-color: #f0f2f5; }
        #reader { width: 500px; border: none; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.1); background: white; }
        @media(max-width: 600px) { #reader { width: 320px; } }
        h1 { color: #1a73e8; margin-bottom: 20px; }
        .status-msg { margin-top: 15px; color: #555; font-size: 0.9rem; }
    </style>
</head>
<body>

    <h1>Scan here</h1>
    <div id="reader"></div>
    <p class="status-msg">Align QR code within the frame to scan</p>

    <script>
        // Initialize jsPDF
        const { jsPDF } = window.jspdf;

        function generatePDF(data) 
        {
            // 1. Initialize jsPDF with ID card dimensions (Portrait: 54mm x 86mm)
            // If you want Landscape, use: orientation: 'landscape', format: [86, 54]
            const doc = new jspdf.jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: [54, 86]
            });

            const bgImage = new Image();
            bgImage.src = 'img/backgroundID.jpeg';

            // Wait for image to load before generating PDF
            bgImage.onload = function() {
                // 2. Add Background Image (Full card coverage)
                doc.addImage(bgImage, 'JPEG', 0, 0, 54, 86);

                // 3. Header Text (Optional - e.g., Event Name)
                doc.setFontSize(40);
                doc.setTextColor(255, 255, 255); // White text
                //doc.text("GDG BACOLOD 2024", 27, 12, { align: "center" });

                // 4. Main Content (The QR Code Data)
                // We trim the data to remove any accidental spaces that mess up centering
                const cleanData = data.trim();
                
                doc.setFontSize(40);
                doc.setTextColor(0, 0, 0); // Black text
                
                // Wrap text so it doesn't go off the edges (44mm width gives 5mm margins)
                const maxWidth = 44;
                const splitText = doc.splitTextToSize(cleanData, maxWidth);
                
                // 27 is the center of the 54mm card
                // 50 is the vertical (Y) position. Adjust this to move text up or down.
                doc.text(splitText, 22, 64, { align: "center" });

                // 5. Add a timestamp at the bottom
                doc.setFontSize(7);
                doc.setTextColor(100);
                //doc.text(`Generated: ${new Date().toLocaleDateString()}`, 27, 80, { align: "center" });

                // 6. Action Handlers
                const action = confirm("Click OK to PRINT ID. Cancel to DOWNLOAD PDF.");
                
                if (action) {
                    doc.autoPrint();
                    window.open(doc.output('bloburl'), '_blank');
                } else {
                    doc.save("event-id.pdf");
                }
            };

            bgImage.onerror = function() {
                alert("Error: Background image not found at 'img/backgroundID.jpeg'. Please check your file path.");
            };
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Stop scanner to prevent multiple scans of the same code
            html5QrcodeScanner.clear();
            
            // Trigger PDF Generation
            generatePDF(decodedText);

            // Optional: Restart scanner after a delay if needed
            setTimeout(() => {
                location.reload(); // Simplest way to reset the scanner UI
            }, 3000);
        }

        function onScanFailure(error) {
            // Ignore failure logs to keep console clean
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", 
            { fps: 10, qrbox: {width: 250, height: 250} },
            /* verbose= */ false
        );

        html5QrcodeScanner.render(onScanSuccess, onScanFailure);
    </script>
</body>
</html>