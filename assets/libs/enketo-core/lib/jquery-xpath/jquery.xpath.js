/**
 * Bunch of XPath tools used in Enketo Smart Paper
 */

( function( factory ) {
    if ( typeof define === 'function' && define.amd ) {
        define( [ 'jquery' ], factory );
    } else {
        factory( jQuery );
    }
}( function( $ ) {

    /**
     * Creates an XPath from a node
     * @param  {string=} rootNodeName   if absent the root is #document
     * @param  {boolean=} includePosition whether or not to include the positions /path/to/repeat[2]/node
     * @return {string}                 XPath
     */
    $.fn.getXPath = function( rootNodeName, includePosition ) {
        //other nodes may have the same XPath but because this function is used to determine the corresponding input name of a data node, index is not included 
        var $sibSameNameAndSelf,
            steps = [],
            position = '',
            $node = this.first(),
            nodeName = $node.prop( 'nodeName' ),
            $parent = $node.parent(),
            parentName = $parent.prop( 'nodeName' );

        rootNodeName = rootNodeName || '#document';
        includePosition = includePosition || false;

        if ( includePosition ) {
            $sibSameNameAndSelf = $node.siblings( nodeName ).addBack();
            position = ( $sibSameNameAndSelf.length > 1 ) ? '[' + ( $sibSameNameAndSelf.index( $node ) + 1 ) + ']' : '';
        }

        steps.push( nodeName + position );

        while ( $parent.length == 1 && parentName !== rootNodeName && parentName !== '#document' ) {
            if ( includePosition ) {
                $sibSameNameAndSelf = $parent.siblings( parentName ).addBack();
                position = ( $sibSameNameAndSelf.length > 1 ) ? '[' + ( $sibSameNameAndSelf.index( $parent ) + 1 ) + ']' : '';
            }
            steps.push( parentName + position );
            $parent = $parent.parent();
            parentName = $parent.prop( 'nodeName' );
        }
        return '/' + steps.reverse().join( '/' );
    };

    /**
     * Simple XPath Compatibility Plugin for jQuery 1.1
     * By John Resig
     * Dual licensed under MIT and GPL.
     * Original plugin code here: http://code.google.com/p/jqueryjs/source/browse/trunk/plugins/xpath/jquery.xpath.js?spec=svn3167&r=3167
     * some changes made by Martijn van de Rijdt (not replacing $.find(), removed context, dot escaping)
     *
     * @param  {string} selector [description]
     * @return {?(Array.<(Element|null)>|Element)}          [description]
     */
    $.fn.xfind = function( selector ) {
        var parts, cur, i;

        // Convert // to " "
        selector = selector.replace( /\/\//g, " " );

        //added by Martijn
        selector = selector.replace( /^\//, "" );
        selector = selector.replace( /\/\.$/, "" );

        // Convert / to >
        selector = selector.replace( /\//g, ">" );

        // Naively convert [elem] into :has(elem)
        selector = selector.replace( /\[([^@].*?)\]/g, function( m, selector ) {
            return ":has(" + selector + ")";
        } );

        // Naively convert /.. into a new set of expressions
        // Martijn: I just don't see this except if this always occurs as nodea/../../parentofnodea/../../grandparentofnodea
        if ( selector.indexOf( ">.." ) >= 0 ) {
            parts = selector.split( />\.\.>?/g );
            //var cur = jQuery(parts[0], context);
            cur = jQuery( parts[ 0 ], this );
            for ( i = 1; i < parts.length; i++ )
                cur = cur.parent( parts[ i ] );
            return cur.get();
        }

        // any remaining dots inside node names need to be escaped (added by Martijn)
        selector = selector.replace( /\./gi, '\\.' );

        //if performance becomes an issue, it's worthwhile implementing this with native XPath instead.
        return this.find( selector );
    };

} ) );
