import { __ } from '@wordpress/i18n';
import './editor.scss';
import Heroicon from "./components/Heroicon";

import {
    useBlockProps,
    InspectorControls,
} from '@wordpress/block-editor';

import {
    __experimentalNumberControl as NumberControl,
    __experimentalToggleGroupControl as ToggleGroupControl,
    __experimentalToggleGroupControlOptionIcon as ToggleGroupControlOptionIcon,
    PanelBody,
    PanelRow,
} from '@wordpress/components';

import ServerSideRender from '@wordpress/server-side-render';

export default function Edit( { attributes, setAttributes } ) {
    const {
        icon,
        iconWidth,
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

	return (
        <>
            <InspectorControls>
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

            <div { ...useBlockProps() }>
                <ServerSideRender
                    block="roelmagdaleno/like-post-block"
                    attributes={ attributes }
                />
            </div>
        </>
	);
}
