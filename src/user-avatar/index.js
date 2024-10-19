import metadata from "./block.json";
import Edit from "./Edit";
import Save from "./Save";
import { UserAvatarIcon } from "./Icon";
export const name = metadata.name;
export const settings = {
	...metadata,
	icon: UserAvatarIcon,
	edit: Edit,
	save: Save
};
