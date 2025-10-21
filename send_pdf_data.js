// This function collects data from the page and sends it to the server
async function sendForServerPDFGeneration() {
    // Helper function for a custom alert box
    const showCustomAlert = (message) => {
        const existingAlert = document.getElementById('custom-alert-box');
        if (existingAlert) {
            document.body.removeChild(existingAlert);
        }

        const alertBox = document.createElement('div');
        alertBox.id = 'custom-alert-box';
        alertBox.style.cssText = `
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: white;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            z-index: 1000;
            text-align: center;
        `;

        const alertMessage = document.createElement('p');
        alertMessage.innerText = message;
        alertBox.appendChild(alertMessage);

        const closeButton = document.createElement('button');
        closeButton.innerText = 'OK';
        closeButton.style.cssText = `
            margin-top: 10px;
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            background-color: #007bff;
            color: white;
            cursor: pointer;
        `;
        closeButton.onclick = () => document.body.removeChild(alertBox);
        alertBox.appendChild(closeButton);

        document.body.appendChild(alertBox);
    };

    // Check for necessary elements
    if (typeof html2canvas === 'undefined') {
        showCustomAlert("PDF generation requires html2canvas, please ensure it's loaded.");
        return;
    }

    // Show loading indicator
    document.getElementById("loader").style.display = "block";

    // Show all content tabs to ensure all elements are in the DOM and visible for data collection.
    showAllContent();

    // Hide any UI elements that should not be in the PDF.
    document.getElementById('column-menu').style.display = 'none';
    document.getElementById('jumpDropdown').style.display = 'none';

    // Helper to convert a DOM node to a base64 image string
    const domToBase64Image = async (node) => {
        if (!node) return null;
        try {
            const canvas = await html2canvas(node, { scale: 1.5, useCORS: true, backgroundColor: '#fff' });
            return canvas.toDataURL('image/webp', 0.98);
        } catch (error) {
            console.error("Failed to capture image:", error);
            return null;
        }
    };

    // Helper to convert div table to a simple HTML string
    const convertDivTableToHtml = (divTableElement) => {
        if (!divTableElement) return '';
        return divTableElement.outerHTML;
    };

    const data = {
        birthInfo: convertDivTableToHtml(document.getElementById('birth_info')),
        mainChartImage: await domToBase64Image(document.getElementById('chart')),
        navamsamChartImage: await domToBase64Image(document.getElementById('d9_chart')),
        bhavaChartImage: await domToBase64Image(document.getElementById('bhava_chart')),
        starlord: convertDivTableToHtml(document.getElementById('starlordHeadMutable')) + convertDivTableToHtml(document.getElementById('starlordBodyMutable')),
        gocharam: convertDivTableToHtml(document.getElementById('starlordHeadMutable_transit')) + convertDivTableToHtml(document.getElementById('starlordBodyMutable_transit')),
        thithiYogamKaranam: convertDivTableToHtml(document.getElementById('thithiYogamKaranamHeadMutable')) + convertDivTableToHtml(document.getElementById('thithiYogamKaranamBodyMutable')),
        abhijit: convertDivTableToHtml(document.getElementById('abhijitHeadMutable')) + convertDivTableToHtml(document.getElementById('abhijitBodyMutable')),
        kp: convertDivTableToHtml(document.getElementById('kpHeadMutable')) + convertDivTableToHtml(document.getElementById('kpBodyMutable')),
        dasa: convertDivTableToHtml(document.getElementById('DasaMutable')),
        bukthi: convertDivTableToHtml(document.getElementById('BukthiMutable')),
        vargaCharts: {},
        ashtaCharts: {},
        parigaaram: convertDivTableToHtml(document.getElementById('allParigaaramDiv')),
    };

    // Collect all varga and ashta chart images
    const vargaCharts = [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 16, 20, 24, 27, 30, 40, 45, 60];
    for (const d of vargaCharts) {
        data.vargaCharts[`D${d}`] = await domToBase64Image(document.getElementById(`D${d}`));
    }
    const ashtaCharts = [1, 2, 3, 4, 5, 6, 7, 8, 9];
    for (const d of ashtaCharts) {
        data.ashtaCharts[`a${d}`] = await domToBase64Image(document.getElementById(`a${d}`));
    }

    try {
        const response = await fetch('generate_pdf.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data),
        });

        if (!response.ok) {
            throw new Error(`Server returned status: ${response.status}`);
        }

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'birth_chart.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);

    } catch (error) {
        console.error("PDF generation failed:", error);
        showCustomAlert("PDF generation failed. Please try again or contact support.");
    } finally {
        // Clean up
        document.getElementById("loader").style.display = 'none';
        document.getElementById('column-menu').style.display = 'block';
        document.getElementById('jumpDropdown').style.display = 'flex';
    }
}
