import JsBarcode from 'jsbarcode';

document.addEventListener('DOMContentLoaded', () => {
    const barcodeContainers = document.querySelectorAll<HTMLElement>('[data-barcode]');

    barcodeContainers.forEach((container) => {
        const value = container.dataset.barcode;
        if (!value) return;

        const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
        container.appendChild(svg);

        JsBarcode(svg, value, {
            format: 'CODE128',
            width: 2,
            height: 60,
            displayValue: true,
            fontSize: 14,
            margin: 10,
        });
    });

    const printBtn = document.getElementById('barcode-print-btn');
    if (printBtn) {
        printBtn.addEventListener('click', () => {
            window.print();
        });
    }
});
