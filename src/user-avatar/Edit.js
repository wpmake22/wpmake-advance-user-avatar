import React from "react";
import { __ } from "@wordpress/i18n";
import {
	TextControl,
	SelectControl,
	PanelBody,
	Disabled
} from "@wordpress/components";
import { InspectorControls, useBlockProps } from "@wordpress/block-editor";
import { ChakraProvider, Box } from "@chakra-ui/react";

const ServerSideRender = wp.serverSideRender
	? wp.serverSideRender
	: wp.components.ServerSideRender;

function Edit(props) {
	const useProps = useBlockProps();
	const {
		attributes: { blockType },
		setAttributes
	} = props;

	const selectBlockType = (type) => {
		setAttributes({ blockType: type });
	};

	return (
		<ChakraProvider>
			<InspectorControls key="wpmake-aua-gutenberg-avatar-inspector-controls">
				<PanelBody
					title={__("Avatar Settings", "wpmake-advance-user-avatar")}
				>
					<SelectControl
						key="wpmake-aua-gutenberg-avatar-form"
						value={blockType}
						options={[
							{
								label: __(
									"Avatar Uploader",
									"wpmake-advance-user-avatar"
								),
								value: "uploader"
							},
							{
								label: __(
									"Display Avatar",
									"wpmake-advance-user-avatar"
								),
								value: "normal"
							}
						]}
						onChange={selectBlockType}
					/>
				</PanelBody>
			</InspectorControls>
			<Box {...useProps}>
				<Disabled>
					<ServerSideRender
						key="wpmake-gutenberg-user-avatar-server-side-renderer"
						block="wpmake-aua/user-avatar"
						attributes={props.attributes}
					/>
				</Disabled>
			</Box>
		</ChakraProvider>
	);
}

export default Edit;
