/**
 * Meshopt decoder shim for ExploreXR.
 *
 * model-viewer-umd.js bundles MeshoptDecoder (meshoptimizer 0.18) internally.
 * Setting ModelViewerElement.meshoptDecoderLocation causes the UMD to fetch
 * this file; the bundled decoder is what actually runs.
 * This file only needs to be fetchable — it is not executed as user code.
 */
