import { __ } from '@wordpress/i18n';
import './editor.scss';
import Heroicon from "./components/Heroicon";

import {
    __experimentalColorGradientSettingsDropdown as ColorGradientSettingsDropdown,
    __experimentalUseMultipleOriginColorsAndGradients as useMultipleOriginColorsAndGradients,
    InspectorControls,
    useBlockProps,
    withColors,
} from '@wordpress/block-editor';

import {
    __experimentalNumberControl as NumberControl,
    __experimentalToggleGroupControl as ToggleGroupControl,
    __experimentalToggleGroupControlOptionIcon as ToggleGroupControlOptionIcon,
    PanelBody,
    PanelRow,
    ToggleControl,
} from '@wordpress/components';

import ServerSideRender from '@wordpress/server-side-render';

export function Edit( { attributes, setAttributes, iconColor, setIconColor, clientId } ) {
    const {
		unlimited,
        icon,
        iconColorValue,
        iconWidth,
        limit,
        renderWithAjax,
    } = attributes;

    const HandThumbUpIcon = (
        <Heroicon
            component="HandThumbUpIcon"
            type="outline"
            width={ 25 }
            height={ 25 }
        />
    );

    const HeartIcon = (
        <Heroicon
            component="HeartIcon"
            type="outline"
            width={ 25 }
            height={ 25 }
        />
    );

    const colorGradientSettings = useMultipleOriginColorsAndGradients();

	return (
        <>
            <InspectorControls>
                <PanelBody>
                    <PanelRow>
                        <NumberControl
                            label={ __( 'Limit', 'like-post-block' ) }
                            value={ limit }
                            min={ 1 }
                            onChange={ ( limit ) => setAttributes( { limit: parseInt( limit ) } ) }
							disabled={ unlimited }
                            help={ __( 'Limit the number of likes per user.', 'like-post-block' ) }
                        />
                    </PanelRow>
					<PanelRow>
						<ToggleControl
							label={ __( 'Unlimited', 'like-post-block' ) }
							checked={ unlimited }
							onChange={ ( unlimited ) => setAttributes( { unlimited } ) }
							help={ __( 'Allow users to like the post without limit.', 'like-post-block' ) }
						/>
					</PanelRow>
                    <PanelRow>
                        <ToggleControl
                            label={ __( 'Render with AJAX', 'like-post-block' ) }
                            checked={ renderWithAjax }
                            onChange={ ( renderWithAjax ) => setAttributes( { renderWithAjax: renderWithAjax } ) }
                            help={ __( 'If you are using a caching system, enabling this feature will avoid from being cached. The count will show after your page is rendered.', 'like-post-block' ) }
                        />
                    </PanelRow>
                </PanelBody>
                <PanelBody title={ __( 'Icon', 'like-post-block' ) }>
                    <PanelRow title="Select Icon">
                        <ToggleGroupControl
                            __nextHasNoMarginBottom
                            label={ __( 'Icon', 'like-post-block' ) }
                            onChange={ ( icon ) => setAttributes( { icon: icon } ) }
                            value={ icon }
                        >
                            <ToggleGroupControlOptionIcon
                                icon={ HandThumbUpIcon }
                                label={ __( 'Thumb Up', 'like-post-block' ) }
                                value="HandThumbUpIcon"
                            />
                            <ToggleGroupControlOptionIcon
                                icon={ HeartIcon }
                                label={ __( 'Heart', 'like-post-block' ) }
                                value="HeartIcon"
                            />
                        </ToggleGroupControl>
                    </PanelRow>
                    <PanelRow title="Icon Width">
                        <NumberControl
                            label={ __( 'Width', 'like-post-block' ) }
                            value={ iconWidth }
                            min={ 0 }
                            onChange={ ( iconWidth ) => setAttributes( { iconWidth: parseInt( iconWidth ) } ) }
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
            <InspectorControls group="color">
                <ColorGradientSettingsDropdown
                    __experimentalIsRenderedInSidebar
                    settings={ [
                        {
                            colorValue: iconColor.color || iconColorValue,
                            label: __( 'Icon', 'like-post-block' ),
                            onColorChange: ( colorValue ) => {
                                setIconColor( colorValue );
                                setAttributes( { iconColorValue: colorValue } );
                            },
                            isShownByDefault: true,
                            resetAllFilter: () => {
                                setIconColor( undefined );
                                setAttributes( { iconColorValue: undefined } );
                            },
                        },
                    ] }
                    __experimentalHasMultipleOrigins={ true }
                    panelId={ clientId }
                    { ...colorGradientSettings }
                />
            </InspectorControls>

            <div { ...useBlockProps() }>
                <ServerSideRender
                    block="roelmagdaleno/like-post-block"
                    attributes={ attributes }
                />
            </div>
        </>
	);
}

const iconColorAttributes = {
    iconColor: 'icon-color',
};

export default withColors( iconColorAttributes )( Edit );
