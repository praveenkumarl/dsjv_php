/**
 * Universal PDF Bridge for DSJV App
 * Converts #onepage element into a PDF and communicates with Flutter WebView Bridge.
 */
async function generateAppPdf(customFilename) {
    try {
        var element = document.getElementById('onepage') || document.body;

        if (typeof html2pdf === 'undefined') {
            console.error('html2pdf library is not loaded');
            if (window.FlutterPdfBridge) {
                FlutterPdfBridge.postMessage(JSON.stringify({ error: 'html2pdf_not_found' }));
            }
            return false;
        }

        var filename = customFilename || (typeof PDF_FILENAME !== 'undefined' ? PDF_FILENAME : 'document.pdf');
        if (!filename.toLowerCase().endsWith('.pdf')) {
            filename += '.pdf';
        }

        var opt = {
            margin: [4, 4, 4, 4],
            filename: filename,
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: {
                scale: 2,
                useCORS: true,
                logging: false,
                windowWidth: Math.max(document.documentElement.clientWidth, element.scrollWidth),
                windowHeight: Math.max(document.documentElement.clientHeight, element.scrollHeight),
                onclone: function(clonedDocument) {
                    var progressOverlay = clonedDocument.getElementById('panchangPdfProgress');
                    if (progressOverlay) {
                        progressOverlay.remove();
                    }
                }
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        var dataUri = await html2pdf().set(opt).from(element).outputPdf('datauristring');

        if (window.FlutterPdfBridge) {
            FlutterPdfBridge.postMessage(JSON.stringify({
                status: 'success',
                filename: filename,
                base64: dataUri
            }));
            return true;
        } else {
            var base64 = dataUri.substring(dataUri.indexOf(',') + 1);
            var binary = window.atob(base64);
            var bytes = new Uint8Array(binary.length);
            for (var i = 0; i < binary.length; i++) {
                bytes[i] = binary.charCodeAt(i);
            }

            var pdfBlob = new Blob([bytes], { type: 'application/pdf' });
            var downloadUrl = URL.createObjectURL(pdfBlob);
            var downloadLink = document.createElement('a');
            downloadLink.href = downloadUrl;
            downloadLink.download = filename;
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            downloadLink.remove();
            window.setTimeout(function() {
                URL.revokeObjectURL(downloadUrl);
            }, 3000);
            return true;
        }
    } catch (err) {
        console.error('PDF Generation Error:', err);
        if (window.FlutterPdfBridge) {
            FlutterPdfBridge.postMessage(JSON.stringify({ error: err.toString() }));
        }
        return false;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('onepage') && window.FlutterPdfBridge) {
        FlutterPdfBridge.postMessage(JSON.stringify({
            type: 'pdf_available',
            filename: typeof PDF_FILENAME !== 'undefined' ? PDF_FILENAME : 'document.pdf'
        }));
    }
});
