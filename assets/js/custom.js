(function( $ ) {
	'use strict';
    var editor, editorSettings;

    if ( ! $( '.xscss-content' ).length ) {
        return;
    }
    editorSettings = wp.codeEditor.defaultSettings ? _.clone( wp.codeEditor.defaultSettings ) : {};

    editorSettings.codemirror = _.extend( {}, editorSettings.codemirror, {
        lineNumbers: true,
        lineWrapping: true,
        indentUnit: 2,
        tabSize: 2,
        mode: 'css',
        lint: true,
        gutters: ['CodeMirror-lint-markers']
    } );

    editor = wp.codeEditor.initialize( $( '.xscss-content' ), editorSettings );
})( jQuery );
