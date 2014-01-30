describe( "$.getXPath", function() {
    $doc = $( $.parseXML( "<root><path><to><node/><repeat><number/></repeat><repeat><number/><number/></repeat></to></path></root>" ) );

    it( 'returns /root/path/to/node without parameters', function() {
        var $node = $doc.find( 'node' );
        expect( $node.getXPath() ).toEqual( '/root/path/to/node' );
    } );
    it( 'returns same /root/path/to/node if first parameter is null', function() {
        var $node = $doc.find( 'node' );
        expect( $node.getXPath( null ) ).toEqual( '/root/path/to/node' );
    } );
    it( 'returns path from context first node provided as parameter', function() {
        var $node = $doc.find( 'node' );
        expect( $node.getXPath( 'root' ) ).toEqual( '/path/to/node' );
    } );
    it( 'returned path includes no positions if there are no siblings with the same name along the path', function() {
        var $node = $doc.find( 'node' );
        expect( $node.getXPath( 'root', true ) ).toEqual( '/path/to/node' );
    } );
    it( 'returned path includes positions when asked', function() {
        var $node = $doc.find( 'number:eq(1)' );
        expect( $node.getXPath( 'root', true ) ).toEqual( '/path/to/repeat[2]/number[1]' );
    } );
    it( 'returned path includes positions when asked (multiple levels)', function() {
        var $node = $doc.find( 'number:eq(2)' );
        expect( $node.getXPath( 'root', true ) ).toEqual( '/path/to/repeat[2]/number[2]' );
    } );
} );

describe( "$.xfind", function() {
    $doc = $( $.parseXML( "<root><path><to><node>value</node><repeat><number>1</number></repeat><repeat><number>2</number><number>3</number></repeat></to></path></root>" ) );

    it( 'finds simple /root/path/to/node', function() {
        expect( $doc.xfind( '/root/path/to/node' ).text() ).toEqual( 'value' )
    } );
    it( 'finds //node', function() {
        expect( $doc.xfind( '//node' ).text() ).toEqual( 'value' )
    } );
    it( 'finds node', function() {
        expect( $doc.xfind( 'node' ).text() ).toEqual( 'value' )
    } );
    it( 'does not find /node', function() {
        expect( $doc.xfind( '/node' ).length ).toEqual( 0 )
    } );
    it( 'finds multiple nodes', function() {
        expect( $doc.xfind( '//number' ).length ).toEqual( 3 )
    } );
    //it( 'finds /path/to/repeat[1]/number[1]', function() {
    //    expect( $doc.xfind( '/path/to/repeat[1]/number[1]' ).text() ).toEqual( 1 )
    //} );
    //
    //$node.find('../../root')
} )
