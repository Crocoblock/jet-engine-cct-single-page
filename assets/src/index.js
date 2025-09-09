import { createRoot, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
	Panel,
	PanelBody,
	TextControl,
	SelectControl,
	Button,
	Notice,
} from '@wordpress/components';

import SettingsItem from './item';
import './style.scss';

const App = () => {

	const initial = (window.JET_CCT_ADMIN_DATA && window.JET_CCT_ADMIN_DATA.settings) || [];
	const [items, setItems] = useState(initial);
	const [saving, setSaving] = useState(false);
	const [notice, setNotice] = useState(null);

	const save = async () => {

		setSaving(true);
		setNotice(null);

		// Validate before save
		for (let i = 0; i < items.length; i++) {
			const item = items[i];

			if (!item.rewrite_base || item.rewrite_base.trim() === '') {
				setNotice({ status: 'error', message: `Item ${i + 1}: Rewrite base cannot be empty.` });
				setSaving(false);
				// Focus on the first invalid field
				document.querySelector(`#jet-cct-admin-item-base-${i}`).focus();
				return;
			}
			if (!item.cct_id || parseInt(item.cct_id, 10) === 0) {
				setNotice({ status: 'error', message: `Item ${i + 1}: Content Type must be selected.` });
				setSaving(false);
				// Focus on the first invalid field
				document.querySelector(`#jet-cct-admin-item-cct-${i}`).focus();
				return;
			}
		}

		try {
			const res = await window.fetch(window.JET_CCT_ADMIN_DATA.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
				body: new URLSearchParams({
					action: window.JET_CCT_ADMIN_DATA.action,
					nonce: window.JET_CCT_ADMIN_DATA.nonce,
					data: JSON.stringify(items),
				}).toString()
			});

			const json = await res.json();

			if (!json.success) {
				throw new Error(json.data?.message || 'Save failed');
			}

			setNotice({ status: 'success', message: __('Settings saved.', 'csv') });
		} catch (e) {
			setNotice({ status: 'error', message: e.message || __('Error saving settings.', 'csv') });
		} finally {
			setSaving(false);
		}
	};

	return (
		<div className="jet-cct-admin">
			<h1>CCT Single Page Settings</h1>

			{notice && (
				<Notice status={notice.status} onRemove={() => setNotice(null)} isDismissible>
				{notice.message}
				</Notice>
			)}

			<Panel>
				{items.length === 0 && (
					<p style={{ padding: '0 20px' }}>No CCT configurations found. Click "Add New" to create one.</p>
				)}
				{items.map((item, index) => (
					<SettingsItem
						key={index}
						item={item}
						index={index}
						onUpdate={(updatedItem) => {

							// Remove item if null is passed
							if ( null === updatedItem ) {
								const newItems = items.filter((_, i) => i !== index);
								setItems(newItems);
								return;
							}

							const newItems = [...items];
							newItems[index] = { ...newItems[index], ...updatedItem };
							setItems(newItems);
						}}
					/>
				))}
			</Panel>

			<div style={{
				paddingTop: '16px',
				display: 'flex',
				justifyContent: 'space-between',
				alignItems: 'center'
			}}>
				<Button variant="secondary" onClick={() => setItems([...items, {}])}>
					Add New
				</Button>
				<Button variant="primary" isBusy={saving} onClick={save}>
					{ saving ? 'Saving…' : 'Save Settings' }
				</Button>
			</div>
		</div>
	);
};

const root = createRoot( document.getElementById( 'jet-cct-admin-app' ) );

root.render( <App /> );
