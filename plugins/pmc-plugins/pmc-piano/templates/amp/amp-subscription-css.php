<?php
/**
 * AMP Piano stylesheet: CSS Template for PMC Piano subscriptions modules.
 * - Following css would injected to amp style component.
 */
?>
.piano-modal {
    box-sizing: border-box;
    padding: 1.563rem;

    font-family: Geograph,serif;
    font-style: normal;
    font-weight: normal;

    display: flex;
    flex-direction: column;

    width: 100%;

    background: #FFFFFF;

    box-shadow: 0 0 2rem rgba(0,0,0,0.15);
    border-radius: 1.25rem 1.25rem 0 0;
    position: fixed;
    bottom: 0;
    left: 0;
    z-index: 11;
    min-height:25rem;
}

.piano-modal-container {
	max-width: 35.5rem;
	margin:auto;
}

.piano-modal_dialog {
    height: 90vh;
}

.piano-modal__top {
    flex-grow: 1;
    flex-shrink: 0;

    display: flex;
    flex-direction: column;
}

.piano-modal__content {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.piano-modal__header {
    display: flex;
    flex-grow: 1;
    flex-shrink: 0;
    align-items: center;
    justify-content: center;
}

.piano-modal__body {
    margin-bottom: 1.125rem;
}

.piano-modal__footer {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.piano-modal__image {
    margin: 1.875rem;
    display: flex;
    justify-content: center;
    box-sizing: border-box;
}

.piano-modal__icon-1 {
    background: url( "data:image/svg+xml,%3C%3Fxml version='1.0' encoding='UTF-8' standalone='no'%3F%3E%3Csvg xmlns:dc='http://purl.org/dc/elements/1.1/' xmlns:cc='http://creativecommons.org/ns%23' xmlns:rdf='http://www.w3.org/1999/02/22-rdf-syntax-ns%23' xmlns:svg='http://www.w3.org/2000/svg' xmlns='http://www.w3.org/2000/svg' version='1.1' id='svg8547' viewBox='0 0 299.99999 45.0384' height='12.710837mm' width='84.666664mm'%3E%3Cdefs id='defs8549' /%3E%3Cmetadata id='metadata8552'%3E%3Crdf:RDF%3E%3Ccc:Work rdf:about=''%3E%3Cdc:format%3Eimage/svg+xml%3C/dc:format%3E%3Cdc:type rdf:resource='http://purl.org/dc/dcmitype/StillImage' /%3E%3Cdc:title%3E%3C/dc:title%3E%3C/cc:Work%3E%3C/rdf:RDF%3E%3C/metadata%3E%3Cg transform='translate(121.42857,-315.5573)' id='layer1'%3E%3Cg transform='matrix(2.7308414,0,0,2.7308414,-302.0006,-1753.7233)' id='g8524'%3E%3Cpath d='m 175.97947,774.2372 -24.38375,0 c -3.54375,0 -6.2125,-1.48625 -6.2125,-5.17375 l 0,-5.865 c 0,-3.77875 2.185,-5.4375 6.34375,-5.4375 l 24.2525,0 0,2.29125 -24.2025,0 c -2.255,0 -3.955,0.71625 -3.955,3.195 l 0,5.65125 c 0,1.94 1.40375,3.04875 3.7075,3.04875 l 24.45,0' style='fill:%23171112;fill-opacity:1;fill-rule:nonzero;stroke:none' id='path5430' /%3E%3Cpath d='m 138.78572,761.37345 0,12.86375 -2.50375,0 0,-11.3975' style='fill:%23231f20;fill-opacity:1;fill-rule:nonzero;stroke:none' id='path5432' /%3E%3Cpath d='m 102.94197,761.37345 0,12.85125 2.50375,0 0,-11.385' style='fill:%23171112;fill-opacity:1;fill-rule:nonzero;stroke:none' id='path5434' /%3E%3Cpath d='m 102.94197,757.7447 2.24125,0 15.685,9.5725 16.08,-9.5725 1.82875,0 -0.009,1.3675 -16.42625,9.8275 c -1.035,0.61875 -3.795,-0.26375 -3.795,-0.26375 l -15.605,-9.36625' style='fill:%23171112;fill-opacity:1;fill-rule:nonzero;stroke:none' id='path5436' /%3E%3Cpath d='m 96.43822,763.66845 c 0,2.5025 -2.14625,3.75625 -4.63,3.75625 l -23.18125,0 0,6.805 -2.50375,0 0,-9.0625 25.80125,0 c 1.3775,0 2.09,-0.5225 2.075,-1.82875 l -0.01625,-1.54875 c -0.015,-1.3775 -0.6475,-1.7125 -2.05875,-1.7125 l -25.80125,0 0,-2.33125 26.06375,0 c 2.32375,0 4.25125,0.9275 4.25125,3.28625' style='fill:%23171112;fill-opacity:1;fill-rule:nonzero;stroke:none' id='path5438' /%3E%3Cpath d='m 138.78572,759.1122 -0.001,-1.3675 -1.83625,0 -18.40125,10.93125 0.42125,0.24875 c 1.03625,0.6325 2.33875,0.63875 3.38125,0.015 l 0.45875,-0.27375' style='fill:%23ed1c24;fill-opacity:1;fill-rule:nonzero;stroke:none' id='path5440' /%3E%3C/g%3E%3C/g%3E%3C/svg%3E" )  center no-repeat;
    width: 10rem;
    height: 3rem;
    background-size: 100%;
}

.piano-modal__title {
    line-height: 1.5rem;
    font-size: 1rem;
    text-align: center;
    letter-spacing: 0.01em;

    color: #323232;
}

.piano-modal__description {
    margin-top: 0.563rem;

    line-height: 1.5rem;
    font-size: 0.875rem;
    text-align: center;
    letter-spacing: 0.01em;

    color: rgba(50, 50, 50, 0.6);
}

.piano-modal__signin {
    height: 2.625rem;
    line-height: 2.625rem;

    font-size: 0.75rem;
    text-align: center;
    letter-spacing: 0.02em;

    color: #767676;
}

.piano-modal__button {
    width: 15rem;
    min-width: 9.375rem;

    margin-bottom: 0.75rem;
}

.piano-modal__button_last {
    margin-bottom: 0;
}

.piano-button {
    font-weight: 500;
    line-height: 1.5rem;
    font-size: 0.688rem;
    text-align: center;
    letter-spacing: 0.14em;
    text-transform: uppercase;

    color: #FFFFFF;
    background: #b22a2d;

    padding: 0.75rem 0;
    border-radius: 0.3rem
}

.piano-link {
    color: #b22a2d;
    text-decoration: none;
    padding: 0;
    border: none;
    cursor: pointer;
    background: #FFFFFF;
    display: inline;
}

.piano-gray {
    color: #767676;
}

.piano-footer-sticky {
    width: 100%;

    font-family: Geograph,serif;
    font-style: normal;
    font-weight: normal;

    line-height: 1.5rem;
    font-size: 0.875rem;
    text-align: center;
    letter-spacing: 0.01em;

    display: flex;

    background: #FFFFFF;
}

.piano-footer-sticky__body {
    text-align: left;
    margin: 1.125rem 1.5rem;
    flex-grow: 1;
    flex-shrink: 1;
}

.text-align-center {
	text-align: center;
}

@media screen and (orientation: landscape) {

    .piano-modal__image {
        margin-right: 1.25rem;
    }

    .piano-modal__header {
        margin-top: 0.313rem;
    }

}

@media screen and (orientation: portrait) and (max-height: 480px) {
    .piano-modal__header {
        display: none;
    }

    .piano-modal__body {
        margin-top: 1.25rem;
    }
}
