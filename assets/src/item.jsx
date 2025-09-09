import { useState, useEffect } from '@wordpress/element';
import {
	PanelBody,
	TextControl,
	SelectControl,
	Button,
} from '@wordpress/components';

const SettingsItem = ( { item, onUpdate, index } ) => {

	const [
		rewriteBase,
		setRewriteBase
	] = useState( item.rewrite_base || 'cct-' + Math.floor(Math.random() * 1000) );

	const [
		listingId,
		setListingId
	] = useState(String(item.listing_id || '0'));

	const [
		title,
		setTitle
	] = useState(item.title || '%title%');

	const [
		description,
		setDescription
	] = useState(item.description || '%short_description%');

	const [
		cctID,
		setCctID
	] = useState(item.cct_id || '');

	const [
		slugField,
		setSlugField
	] = useState(item.slug_field || '');

	useEffect( () => {
		onUpdate( {
			rewrite_base: rewriteBase.trim(),
			cct_id: parseInt(cctID || '0', 10) || 0,
			listing_id: parseInt(listingId || '0', 10) || 0,
			slug_field: slugField.trim(),
			title: title,
			description: description,
		} );
	}, [rewriteBase, listingId, title, description, cctID, slugField] );

	return (
		<PanelBody title={ `Item ${index + 1}: ${rewriteBase}` } initialOpen={true}>
			<div className="jet-cct-admin-item__remove">
				<Button
					size="small"
					variant="secondary"
					isDestructive
					onClick={ () => {
						if ( confirm( 'Are you sure you want to remove this item?' ) ) {
							onUpdate( null );
						}
					} }
				>
					Delete
				</Button>
			</div>
			<TextControl
				label="Rewrite base (URL prefix)"
				help="Example: with 'my-cct', your URLs look like /my-cct/some-title-123"
				value={rewriteBase}
				onChange={setRewriteBase}
				id={`jet-cct-admin-item-base-${index}`}
			/>
			<SelectControl
				label="Content Type"
				help="Choose content type to rewrite."
				options={window.JET_CCT_ADMIN_DATA.contentTypes || []}
				value={cctID}
				onChange={setCctID}
				id={`jet-cct-admin-item-cct-${index}`}
			/>
			<SelectControl
				label="Content template"
				help="JetEngine Listing Item used as a single page template."
				options={window.JET_CCT_ADMIN_DATA.listingItems || []}
				value={listingId}
				onChange={setListingId}
			/>
			<TextControl
				label="Slug field"
				help="Set CCT field to use as an item slug in the URL. Spaces in the field value will be replaced with dashes automatically on URL generation. You can add multiple fields separated with commas, each next will be used if the previous is empty. Example: 'slug_field, title_field'"
				value={slugField}
				onChange={setSlugField}
			/>
			<TextControl
				label="Page title pattern"
				help="Use selected CCT's fields in the next format - %title%, %type%, %short_description% etc."
				value={title}
				onChange={setTitle}
			/>
			<TextControl
				label="Meta description pattern"
				help="Use selected CCT's fields in the next format - %short_description%, %title%."
				value={description}
				onChange={setDescription}
			/>

		</PanelBody>
	);
};

export default SettingsItem;
