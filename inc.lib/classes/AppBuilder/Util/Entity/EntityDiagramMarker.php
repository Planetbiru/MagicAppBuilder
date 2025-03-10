<?php

namespace AppBuilder\Util\Entity;

use SVG\Nodes\Shapes\SVGPath;
use SVG\Nodes\Structures\SVGDefs;
use SVG\Nodes\Structures\SVGMarker;

/**
 * Class EntityDiagramMarker
 *
 * This class provides methods to create SVG markers for use in entity diagrams.
 * The markers represent different cardinalities and include an arrow for miscellaneous use.
 */
class EntityDiagramMarker
{

    /**
     * Create SVG markers for various cardinalities.
     *
     * Cardinalities:
     *  - 11: One and only One
     *  - 1M: One or Many
     *  - M: Many
     *  - 01: Zero or One
     *  - 0M: Zero or Many
     *  - arrow: included for miscellaneous use
     *
     * @return SVGDefs The SVG definitions containing the markers.
     */
    public static function createMarker()
    {
        $icondefs = new SVGDefs();
        $icondefs
        ->setStyle("width","100px")
        ->setStyle("height","100px")
        ->setStyle("postion","absolute")
        ->setStyle("left","-120px")
        ;

        $marker1 = new SVGMarker();
        $marker1
        ->setAttribute('id', "1")
        ->setAttribute("viewBox", "0 0 130 130")
        ->setAttribute('markerHeight', 5)
        ->setAttribute('markerWidth', 8)
        ->setAttribute('refX', 60)
        ->setAttribute('refY', 60)
        ->setAttribute("orient", "auto")
        ->setAttribute("class","polymarker")
        ->addChild(new SVGPath(
        "M83->3,69->7 M51->8,0->1L36->9,0l0->3,130h15->1 M48->4,45->9 M-0->4,59->3l-0->5,13->2l90->3,0->4l0->4-13->4 M51->3,69->6")
        )
        ;

        $marker2 = new SVGMarker();
        $marker2
        ->setAttribute('id', "11")
        ->setAttribute("viewBox", "0 0 130 130")
        ->setAttribute('markerHeight', 5)
        ->setAttribute('markerWidth', 8)
        ->setAttribute('refX', 40)
        ->setAttribute('refY', 60)
        ->setAttribute("orient", "auto")
        ->setAttribute("class","polymarker")
        ->addChild(new SVGPath(
        "M83->5,69->7 M68,0->1L53->2,0l0->3,130h15->1 M48->6,45->9 M-0->2,59->3l-0->5,13->2l90->3,0->4l0->4-13->4 M51->5,69->6 M36,0L21->2-0->2 l0->3,130h15->1")
        )
        ;


        $marker3 = new SVGMarker();
        $marker3
        ->setAttribute('id', "M")
        ->setAttribute("viewBox", "0 0 90 130")
        ->setAttribute('markerHeight', 5)
        ->setAttribute('markerWidth', 8)
        ->setAttribute('refX', 60)
        ->setAttribute('refY', 60)
        ->setAttribute("orient", "auto")
        ->setAttribute("class","polymarker")
        ->addChild(new SVGPath(
        "M89->7,114->5L29->4,69->7H90l-0->3-11->3l-60,0->5l60-43->7L90,0->5L3,65l87,64->5L89->7,114->5z")
        )
        ;

        $marker4 = new SVGMarker();
        $marker4
        ->setAttribute('id', "1M")
        ->setAttribute("viewBox", "0 0 90 130")
        ->setAttribute('markerHeight', 5)
        ->setAttribute('markerWidth', 8)
        ->setAttribute('refX', 100)
        ->setAttribute('refY', 60)
        ->setAttribute("orient", "auto")
        ->setAttribute("class","polymarker")
        ->addChild(new SVGPath(
        "M96->4,114->5L36->1,69->7h60->6l-0->3-11->3l-60,0->5l60-43->7l0->3-14->7L9->7,65l87,64->5L96->4,114->5z M12->6,0->3L0->4,0->1l0->2,130h12->5")
        )
        ;

        $marker5 = new SVGMarker();
        $marker5
        ->setAttribute('id', "0M")
        ->setAttribute("viewBox", "0 0 215 130")
        ->setAttribute('markerHeight', 7)
        ->setAttribute('markerWidth', 7)
        ->setAttribute('refX', 200)
        ->setAttribute('refY', 65)
        ->setAttribute("orient", "auto")
        ->setAttribute("class","polymarker")
        ->addChild(new SVGPath(
        "M214->4,114->5l-60->3-44->8h60->6l-0->3-11->3l-60,0->5l60-43->7l0->3-14->7l-87,64->5l87,64->5L214->4,114->5z M129->8,65->4 c0,35->3-28->6,63->9-63->9,63->9S2,100->7,2,65->4S30->6,1->5,65->9,1->5S129->8,30->1,129->8,65->4z M65->9,11->3c-29->9,0-54->1,24->2-54->1,54->1 s24->2,54->1,54->1,54->1S120,95->3,120,65->4S95->8,11->3,65->9,11->3z")
        )
        ;

        $marker6 = new SVGMarker();
        $marker6
        ->setAttribute('id', "01")
        ->setAttribute("viewBox", "0 0 215 130")
        ->setAttribute('markerHeight', 7)
        ->setAttribute('markerWidth', 7)
        ->setAttribute('refX', 200)
        ->setAttribute('refY', 65)
        ->setAttribute("orient", "auto")
        ->setAttribute("class","polymarker")
        ->addChild(new SVGPath(
        "M130->8,65->4c0,35->3-28->6,63->9-63->9,63->9S3,100->7,3,65->4S31->6,1->5,66->9,1->5S130->8,30->1,130->8,65->4z M66->7,14 c-28->3,0-51->3,22->9-51->3,51->3s22->9,51->3,51->3,51->3S118,93->6,118,65->2S95->1,14,66->7,14z M130->1,59->3l-0->5,13->2l98->6,0->5l0->4-13->4 M183->6,5->1l-14->8-0->1l0->3,119->4h15->1")
        )
        ;
        $marker7 = new SVGMarker();
        $marker7
        ->setAttribute("id", "arrow")
        ->setAttribute("viewBox", "0 0 20 20")
        ->setAttribute("refX",0)
        ->setAttribute("refY", 5)
        ->setAttribute("markerWidth", 7)
        ->setAttribute("markerHeight", 7)
        ->setAttribute("orient", "auto")
        ->setAttribute("class","arrow")
        ->addChild(new SVGPath(
        "M0,0 L10,5 L0,10 L2,5 L0,0")
        )
        ;
        
        $icondefs->addChild($marker1);
        $icondefs->addChild($marker2);
        $icondefs->addChild($marker3);
        $icondefs->addChild($marker4);
        $icondefs->addChild($marker5);
        $icondefs->addChild($marker6);
        $icondefs->addChild($marker7);
        
        return $icondefs;
    }
}