import { registerBlockType } from "@wordpress/blocks";
import { applyFilters } from "@wordpress/hooks";
import * as UserAvatar from "./user-avatar";

let blocks = [UserAvatar];
blocks = applyFilters("wpmake-aua.blocks", blocks);

/**
 * The function "registerBlocks" iterates over an array of blocks and calls the
 * "register" method on each block.
 */
const registerBlocks = () => {
	for (const block of blocks) {
		const settings = applyFilters(
			"wpmake-aua.blocks.metadata",
			block.settings
		);
		settings.edit = applyFilters(
			"wpmake-aua.blocks.edit",
			settings.edit,
			settings
		);
		//Register the blocks.
		registerBlockType(block.name, settings);
	}
};

registerBlocks();
