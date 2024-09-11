const { registerBlockType } = wp.blocks;
const { TextControl, SelectControl, CheckboxControl, TextareaControl, PanelBody } = wp.components;
const { InspectorControls, RichText } = wp.blockEditor;
const { __ } = wp.i18n;
const { createElement: el, useState, useEffect, useRef } = wp.element;
const { useSelect } = wp.data;


// ###################################################
// Basic Information Profile Block
// ###################################################

registerBlockType('staff-manager/basic-information', {
    title: __('Staff Profile Information', 'staff-manager'),
    icon: 'admin-users',
    category: 'common',
    attributes: {
        privacyInfo: { type: 'string', default: '' },
        title: { type: 'string', default: '' },
        firstname: { type: 'string', default: '' },
        lastname: { type: 'string', default: '' },
        position: { type: 'string', default: '' },
        affiliations: { type: 'string', default: '' },
        room: { type: 'string', default: '' },
        phone: { type: 'string', default: '' },
        mobile: { type: 'string', default: '' },
        email: { type: 'string', default: '' },
        link1: { type: 'string', default: '' },
        link1text: { type: 'string', default: '' },
        link2: { type: 'string', default: '' },
        link2text: { type: 'string', default: '' },
        link3: { type: 'string', default: '' },
        link3text: { type: 'string', default: '' },
        link4: { type: 'string', default: '' },
        link4text: { type: 'string', default: '' },
        link5: { type: 'string', default: '' },
        link5text: { type: 'string', default: '' },
        address: { type: 'string', default: '' },
        additionalInfo: { type: 'string', default: '' },
        groups: { type: 'array', default: [] }
    },
    edit: function (props) {
        const { attributes, setAttributes, isSelected } = props;
        const { title, firstname, lastname, position, email, mobile, privacyInfo, affiliations, room, phone, address, additionalInfo, groups } = attributes;

        const selectRef = useRef(null);

        const groupOptions = useSelect((select) => {
            const posts = select('core').getEntityRecords('postType', 'groups', { per_page: -1 });

            if (!posts) {
                return [];
            }

            const groupedData = posts.reduce((acc, post) => {
                const parentPost = posts.find(p => p.id === post.parent);
                const group_name = parentPost ? parentPost.title.rendered : post.title.rendered;
                const department_name = post.title.rendered;

                if (!acc[group_name]) {
                    acc[group_name] = [];
                }
                // Only add the department if it is not the same as the group name
                if (department_name !== group_name) {
                    acc[group_name].push(department_name);
                }

                return acc;
            }, {});

            // Create formatted departments and reverse the order
            const formattedDepartments = Object.entries(groupedData).map(([group, departments]) => ({
                label: group,
                options: departments.map(department => ({
                    value: department,
                    label: department
                }))
            })).reverse();

            return formattedDepartments;
        }, []);

        useEffect(() => {
            if (selectRef.current) {
                jQuery(selectRef.current).selectpicker('destroy'); // Destroy previous instance if any
                jQuery(selectRef.current).selectpicker();
            }
        }, [groupOptions]);
        
        const isFieldValid = (field) => field && field.trim().length > 0;
        const isFormValid = isFieldValid(firstname) && isFieldValid(lastname) && isFieldValid(position);

        return (
            el('div', { className: 'bg-light' },
                el('div', { className: 'p-3' },
                    el('h5', { className: 'border-bottom pb-3' }, 'Staff Profile Information')
                ),
                el('div', { className: props.className },
                    el('div', { className: 'p-3' },
                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Privacy Info', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el(TextareaControl, {
                                    value: privacyInfo,
                                    onChange: (value) => setAttributes({ privacyInfo: value })
                                })
                            )
                        ),
                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Title', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el(TextControl, {
                                    value: title,
                                    onChange: (value) => setAttributes({ title: value })
                                })
                            )
                        ),
                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('First Name', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el(TextControl, {
                                    value: firstname,
                                    onChange: (value) => setAttributes({ firstname: value })
                                })
                            )
                        ),
                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Last Name', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el(TextControl, {
                                    value: lastname,
                                    onChange: (value) => setAttributes({ lastname: value })
                                })
                            )
                        ),
                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Position', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el(TextControl, {
                                    value: position,
                                    onChange: (value) => setAttributes({ position: value })
                                })
                            )
                        ),
                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Affiliations', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el(RichText, {
                                    tagName: 'div', // or 'p', 'div', 'section', etc. depending on the desired HTML tag
                                    value: affiliations,
                                    onChange: (value) => setAttributes({ affiliations: value }),
                                    //placeholder: __('Write additional information...', 'staff-manager'), // Optional placeholder text
                                    style: { minHeight: '200px', overflow: 'auto' }, // Set minimum height and scroll overflow
                                    className: 'form-control', // Optional additional class
                                })
                            )
                        ),

                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Room', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el(TextControl, {
                                    value: room,
                                    onChange: (value) => setAttributes({ room: value })
                                })
                            )
                        ),
                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Phone', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el(TextControl, {
                                    value: phone,
                                    onChange: (value) => setAttributes({ phone: value })
                                })
                            )
                        ),
                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Mobile', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el(TextControl, {
                                    value: mobile,
                                    onChange: (value) => setAttributes({ mobile: value })
                                })
                            )
                        ),
                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Email', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el(TextControl, {
                                    type: 'email',
                                    value: email,
                                    onChange: (value) => setAttributes({ email: value })
                                })
                            )
                        ),
                        ['link1', 'link2', 'link3', 'link4', 'link5'].map((url, index) => (
                            el('div', { key: index, className: 'mb-3 row' },
                                el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Link ' + (index + 1), 'staff-manager')),
                                el('div', { className: 'col-sm-5' },
                                    el(TextControl, {
                                        type: 'url',
                                        value: attributes[url],
                                        onChange: (value) => setAttributes({ [url]: value })
                                    })
                                ),
                                el('label', { className: 'col-sm-1 col-form-label fw-bold' }, __('Text', 'staff-manager')),
                                el('div', { className: 'col-sm-4' },
                                    el(TextControl, {
                                        value: attributes[url + 'text'],
                                        onChange: (value) => setAttributes({ [url + 'text']: value })
                                    })
                                )
                            )
                        )),

                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Address', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el(RichText, {
                                    tagName: 'div', // or 'p', 'div', 'section', etc. depending on the desired HTML tag
                                    value: address,
                                    onChange: (value) => setAttributes({ address: value }),
                                    //placeholder: __('Write additional information...', 'staff-manager'), // Optional placeholder text
                                    style: { minHeight: '200px', overflow: 'auto' }, // Set minimum height and scroll overflow
                                    className: 'form-control', // Optional additional class
                                })
                            )
                        ),
                        
                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Additional Info', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el(RichText, {
                                    tagName: 'div', // or 'p', 'div', 'section', etc. depending on the desired HTML tag
                                    value: additionalInfo,
                                    onChange: (value) => setAttributes({ additionalInfo: value }),
                                    //placeholder: __('Write additional information...', 'staff-manager'), // Optional placeholder text
                                    style: { minHeight: '200px', overflow: 'auto' }, // Set minimum height and scroll overflow
                                    className: 'form-control', // Optional additional class
                                })
                            )
                        ),

                        el('div', { className: 'mb-3 row' },
                            el('label', { className: 'col-sm-2 col-form-label fw-bold' }, __('Groups', 'staff-manager')),
                            el('div', { className: 'col-sm-10' },
                                el('select', {
                                    ref: selectRef,
                                    multiple: true,
                                    className: 'selectpicker',
                                    value: groups || [],
                                    onChange: (event) => {
                                        const values = Array.from(event.target.selectedOptions).map(option => option.value);
                                        setAttributes({ groups: values });
                                    },
                                    'data-live-search': true
                                },
                                    groupOptions.map((group, index) =>
                                        el('optgroup', { label: group.label, key: index }, // Use index for unique keys
                                            group.options.map(option =>
                                                el('option', { key: option.value, value: option.value }, option.label)
                                            )
                                        )
                                    )
                                )
                            )
                        )
                        
                    )
                ),
                !isFormValid && isSelected && el('div', { className: 'error' }, __('Please fill in all required fields', 'staff-manager'))
            )
        );
    },
    save: function () {
        return null; // Content is rendered dynamically in PHP
    }
});


// ###################################################
// Profilliste (vollständig)
// ###################################################

registerBlockType('staff-manager/all-staff', {
    title: 'Staff list (all)',
    icon: 'admin-users',
    category: 'widgets',
    edit: function() {
        return el('div', { className: 'border p-2' },
                    el('div', { className: 'fw-bold' }, 'Staff list (all)')
                );
    },
    save: function() {
        return null; // Content is rendered dynamically in PHP
    }
});

// ###################################################
// Kurzprofil (Arbeitsgruppe)
// ###################################################

registerBlockType('staff-manager/group-staff', {
    title: 'Staff list (group)',
    icon: 'admin-users',
    category: 'widgets',
    attributes: {
        id: {
            type: 'number',
            default: 0,
        },
    },
    edit: function (props) {
        const { attributes, setAttributes } = props;
        const { id } = attributes;

        const selectRef = useRef(null);

        const groupOptions = useSelect((select) => {
            const posts = select('core').getEntityRecords('postType', 'groups', { per_page: -1 });
        
            if (!posts) {
                return [];
            }
        
            const groupedData = posts.reduce((acc, post) => {
                const parentPost = posts.find(p => p.id === post.parent);
                const group_name = parentPost ? parentPost.title.rendered : post.title.rendered;
                const department_name = post.title.rendered;
        
                if (!acc[group_name]) {
                    acc[group_name] = [];
                }
                // Add the department object with id and name
                if (department_name !== group_name) {
                    acc[group_name].push({ id: post.id, name: department_name });
                }
        
                return acc;
            }, {});
        
            // Create formatted departments and reverse the order
            const formattedDepartments = Object.entries(groupedData).map(([group, departments]) => ({
                label: group,
                options: departments.map(department => ({
                    value: department.id, // Use the department ID for the value
                    label: department.name // Use the department name for the label
                }))
            })).reverse();
        
            return formattedDepartments;
        }, []);            

        //console.log(groupOptions);

        useEffect(() => {
                if (selectRef.current) {
                jQuery(selectRef.current).selectpicker('destroy'); // Destroy previous instance if any
                jQuery(selectRef.current).selectpicker();
            }
        }, [groupOptions]);

        // Render the select element with selectpicker
        return el('select', {
            ref: selectRef,
            className: 'selectpicker',
            'data-live-search': true,
            value: id !== 0 ? id : '',
            onChange: (event) => {
                //const selectedValue = event.target.value;
                //console.log('Selected Value:', selectedValue);
                setAttributes({ id: parseInt(event.target.value, 10) });
            }                
            },
            groupOptions.map((group, index) =>
                    el('optgroup', { label: group.label, key: index }, // Use index for unique keys
                        group.options.map(option =>
                        el('option', { key: option.value, value: option.value }, option.label)
                    )
                )
            )
        );
    },
    save: function () {
        return null; // Content is rendered dynamically in PHP
    },
});

// ###################################################
// Kurzprofil (individuell)
// ###################################################

registerBlockType('staff-manager/single-staff', {
    title: 'Staff list (single)',
    icon: 'admin-users',
    category: 'widgets',
    attributes: {
        id: {
            type: 'number',
            default: 0,
        },
    },
    edit: function (props) {
        const { attributes, setAttributes } = props;
        const { id } = attributes;

        const selectRef = useRef(null);

        // Fetch staff options
        const staffOptions = useSelect((select) => {
            const posts = select('core').getEntityRecords('postType', 'staff-manager', { per_page: -1 });

            if (!posts) {
                return [];
            }

            return posts.map((post) => ({
                value: post.id,
                label: post.title.rendered,
            }));
        }, []);

        useEffect(() => {
            if (selectRef.current) {
                jQuery(selectRef.current).selectpicker('destroy'); // Destroy previous instance if any
                jQuery(selectRef.current).selectpicker();
            }
        }, [staffOptions]);

        // Render the select element with selectpicker
        return el('select', {
            ref: selectRef,
            className: 'selectpicker',
            'data-live-search': true,
            value: id !== 0 ? id : '',
            onChange: (event) => {
                setAttributes({ id: parseInt(event.target.value, 10) });
            }
        },
            staffOptions.map((option) =>
                el('option', { value: option.value, key: option.value }, option.label)
            )
        );
    },
    save: function () {
        return null; // Content is rendered dynamically in PHP
    },
});
