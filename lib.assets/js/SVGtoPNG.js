/**
 * Converts an SVG element to a PNG data URL using canvas.
 *
 * @param {SVGElement} svgElement - The SVG element to convert.
 * @returns {Promise<string>} A promise resolving to a PNG data URL.
 * @async
 */
async function convertSvgToPng(svgElement) {
    return new Promise((resolve) => {
        // Clone the SVG element to avoid modifying the original
        const clonedSvg = svgElement.cloneNode(true);

        // Ensure the SVG namespace is explicitly set
        clonedSvg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');

        // Create a style element and embed the desired font styles
        const styleElement = document.createElementNS('http://www.w3.org/2000/svg', 'style');
        // Use a general selector like 'svg' or '*' to apply styles broadly
        // or target specific elements if known. Here, 'svg' targets the root.
        styleElement.textContent = `svg,text{font-family: Arial, sans-serif;font-size: 11px;}`;

        // Find or create a <defs> element to append the style.
        // <defs> is a standard place for definitions like styles, gradients, etc.
        let defs = clonedSvg.querySelector('defs');
        if (!defs) {
            defs = document.createElementNS('http://www.w3.org/2000/svg', 'defs');
            // Prepend defs to ensure it's at the beginning of the SVG content
            clonedSvg.prepend(defs);
        }
        defs.appendChild(styleElement);

        // No longer appending to document.body to avoid disturbing the DOM layout.
        // The SVG will be processed as a detached DOM node.

        // Use requestAnimationFrame to ensure any internal browser rendering queues are processed
        // before serialization, though its impact might be less significant for detached nodes.
        requestAnimationFrame(() => {
            try {
                // Determine the target element for bounding box calculation.
                // Prioritize specific graphic elements, then fall back to the SVG itself.
                let bboxTarget = clonedSvg.querySelector('g, path, rect, circle, ellipse, line, polygon, polyline, text') || clonedSvg;

                // If SVG width/height are not set and getBBox is available, calculate them.
                // Note: getBBox() might not be reliable on detached elements in all browsers.
                // If dimensions are crucial and getBBox() fails, ensure the original SVG
                // has explicit width/height or provide appropriate fallbacks.
                if (
                    (!clonedSvg.hasAttribute('width') || !clonedSvg.hasAttribute('height')) &&
                    typeof bboxTarget.getBBox === 'function'
                ) {
                    try {
                        const bbox = bboxTarget.getBBox();
                        // Ensure width and height are positive, provide fallbacks
                        const width = Math.ceil(bbox.width || 800);
                        const height = Math.ceil(bbox.height || 600);
                        clonedSvg.setAttribute('width', width);
                        clonedSvg.setAttribute('height', height);
                    } catch (e) {
                        console.warn('⚠️ getBBox() failed on detached SVG element, using fallback dimensions.', e);
                        // Fallback to default dimensions if getBBox fails
                        clonedSvg.setAttribute('width', '800');
                        clonedSvg.setAttribute('height', '600');
                    }
                } else if (!clonedSvg.hasAttribute('width') || !clonedSvg.hasAttribute('height')) {
                    // If getBBox is not a function or not available, use default fallbacks
                    clonedSvg.setAttribute('width', clonedSvg.getAttribute('width') || '800');
                    clonedSvg.setAttribute('height', clonedSvg.getAttribute('height') || '600');
                }


                // Serialize the SVG DOM into an XML string
                const serializer = new XMLSerializer();
                let svgString = serializer.serializeToString(clonedSvg);

                // Double-check and ensure the SVG namespace is present at the root
                if (!svgString.match(/^<svg[^>]+xmlns="http:\/\/www\.w3\.org\/2000\/svg"/)) // NOSONAR
                {
                    svgString = svgString.replace(/^<svg/, '<svg xmlns="http://www.w3.org/2000/svg"');
                }

                // Create a Blob from the SVG string
                const svgBlob = new Blob([svgString], { type: 'image/svg+xml;charset=utf-8' });
                // Create an object URL for the Blob
                const url = URL.createObjectURL(svgBlob);

                // Create a new Image object
                const img = new Image();

                // Set up the onload handler for the image
                img.onload = () => {
                    // Create a canvas element
                    const canvas = document.createElement('canvas');
                    // Set canvas dimensions to match the image
                    canvas.width = img.width;
                    canvas.height = img.height;
                    // Get the 2D rendering context
                    const ctx = canvas.getContext('2d');
                    // Draw the SVG image onto the canvas
                    ctx.drawImage(img, 0, 0);

                    // Clean up: revoke the object URL
                    URL.revokeObjectURL(url);

                    // Resolve the promise with the PNG data URL
                    resolve(canvas.toDataURL('image/png'));
                };

                // Set up the onerror handler for the image
                img.onerror = (err) => {
                    console.error('❌ Failed to load SVG image for PNG conversion', err);
                    console.debug('🔍 SVG string that failed:', svgString);
                    // Clean up even on error
                    URL.revokeObjectURL(url);
                    // Resolve with an empty string or reject the promise
                    resolve('');
                };

                // Set the image source to the object URL, triggering the load process
                img.src = url;
            } catch (err) {
                console.error('❌ Error during SVG → PNG conversion:', err);
                // Resolve with an empty string or reject the promise
                resolve('');
            }
        });
    });
}