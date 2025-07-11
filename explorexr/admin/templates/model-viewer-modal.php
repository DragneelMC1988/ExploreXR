<!-- Model viewer modal for previewing 3D models -->
<div id="expoxr-model-modal" class="expoxr-model-modal">
    <div class="expoxr-model-modal-content">
        <span class="expoxr-model-close">&times;</span>
        <h3 id="expoxr-model-title" class="expoxr-model-title">3D Model Preview</h3>        <model-viewer id="expoxr-model-viewer" camera-controls auto-rotate
                     loading="eager"
                     reveal="interaction">
            <div slot="error" class="error" style="
                padding: 20px;
                text-align: center;
                background: #f8f9fa;
                border: 2px dashed #ddd;
                border-radius: 8px;
                color: #666;
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            ">
                <div style="font-size: 48px; margin-bottom: 10px;">📦</div>
                <div style="font-size: 18px; font-weight: 600; margin-bottom: 8px; color: #333;">Unable to display model</div>
                <p class="error-details" style="font-size: 14px; margin-bottom: 15px;">Please check the file path and try again. Contact support if the issue persists.</p>
                <div class="error-actions" style="margin-top: 15px;">
                    <button onclick="document.getElementById('expoxr-model-viewer').setAttribute('src', document.getElementById('expoxr-model-viewer').getAttribute('src') + '?t=' + Date.now())" 
                            style="background: #0073aa; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; margin-right: 10px; font-size: 14px;">
                        Try Again
                    </button>
                    <button onclick="this.closest('[slot=error]').style.display='none'" 
                            style="background: #666; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-size: 14px;">
                        Hide Error
                    </button>
                </div>
                <details style="margin-top: 15px; font-size: 12px; color: #888;">
                    <summary style="cursor: pointer; font-weight: 600;">Troubleshooting Tips</summary>
                    <div style="margin-top: 8px; text-align: left;">
                        • Check if the model file has been deleted or moved<br>
                        • Try uploading the model file again<br>
                        • Verify that the file is in GLB or GLTF format<br>
                        • Check file permissions and server configuration
                    </div>
                </details>
            </div>
        </model-viewer>
    </div>
</div>





