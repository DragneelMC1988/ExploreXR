<!-- Model viewer modal for previewing 3D models -->
<div id="expoxr-model-modal" class="expoxr-model-modal">
    <div class="expoxr-model-modal-content">
        <span class="expoxr-model-close">&times;</span>
        <h3 id="expoxr-model-title" class="expoxr-model-title">3D Model Preview</h3>        <model-viewer id="expoxr-model-viewer" camera-controls auto-rotate
                     loading="eager"
                     reveal="interaction">
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





