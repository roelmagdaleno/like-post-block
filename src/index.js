import { registerBlockType } from '@wordpress/blocks';
import './style.scss';
import Edit from './edit';

registerBlockType( 'roelmagdaleno/like-post-block', { edit: Edit } );
