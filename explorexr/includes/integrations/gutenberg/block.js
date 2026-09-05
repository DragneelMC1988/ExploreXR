/**
 * ExploreXR 3D Model — Gutenberg Block
 *
 * Uses ServerSideRender for live preview of the shortcode output
 * directly inside the block editor.
 *
 * @package ExploreXR
 * @since 1.1.3
 */
( function () {
    'use strict';

    var registerBlockType  = wp.blocks.registerBlockType;
    var InspectorControls  = wp.blockEditor.InspectorControls;
    var PanelBody          = wp.components.PanelBody;
    var SelectControl      = wp.components.SelectControl;
    var ServerSideRender   = wp.serverSideRender;
    var createElement      = wp.element.createElement;

    var models = ( window.explorexrBlockData && window.explorexrBlockData.models ) || [];

    registerBlockType( 'explorexr/model-3d', {
        apiVersion: 3,
        title: 'ExploreXR 3D Model',
        description: 'Display an interactive 3D model.',
        icon: 'format-gallery',
        category: 'media',
        keywords: [ '3d', 'model', 'viewer', 'glb', 'gltf', 'ar', 'explorexr' ],

        attributes: {
            modelId: {
                type: 'string',
                default: ''
            }
        },

        edit: function ( props ) {
            var modelId    = props.attributes.modelId;
            var setModelId = function ( value ) {
                props.setAttributes( { modelId: value } );
            };

            var inspectorPanel = createElement(
                InspectorControls,
                null,
                createElement(
                    PanelBody,
                    { title: '3D Model Settings', initialOpen: true },
                    createElement( SelectControl, {
                        label: 'Select Model',
                        value: modelId,
                        options: models,
                        onChange: setModelId
                    } )
                )
            );

            var preview;

            if ( modelId ) {
                preview = createElement( ServerSideRender, {
                    block: 'explorexr/model-3d',
                    attributes: props.attributes
                } );
            } else {
                preview = createElement(
                    'div',
                    {
                        style: {
                            padding: '40px',
                            textAlign: 'center',
                            background: '#f7f7f7',
                            border: '1px dashed #ccc'
                        }
                    },
                    createElement( 'span', { className: 'dashicons dashicons-format-gallery', style: { fontSize: '40px', display: 'block', marginBottom: '10px' } } ),
                    createElement( 'p', null, 'Select a 3D model from the block settings panel.' )
                );
            }

            return createElement( 'div', null, inspectorPanel, preview );
        },

        save: function () {
            // Server-side rendered — no static save needed.
            return null;
        }
    } );
} )();
