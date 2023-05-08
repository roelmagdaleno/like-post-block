import * as SolidIcons from '@heroicons/react/24/solid';
import * as OutlineIcons from '@heroicons/react/24/outline';

export default function Heroicon( props ) {
	const methods = {
		solid: SolidIcons,
		outline: OutlineIcons,
	};

	const Heroicon = methods[ props.type ][ props.component ];

	if ( ! Heroicon ) {
		return null;
	}

	return (
		<Heroicon
			className="wp-like-post__icon"
			width={ props.width || 30 }
			height={ props.height || 30 }
		/>
	);
}
