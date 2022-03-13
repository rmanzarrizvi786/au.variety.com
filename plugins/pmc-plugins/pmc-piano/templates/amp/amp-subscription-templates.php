<?php
/**
 * Templates for PMC Piano subscription modules.
 *
 * Subscription action evaluates the data
 * - AMP runtime uses `subscriptions-actions` attribute to identify subscription component.
 * - Each element having `subscriptions-actions` attribute should also have `subscriptions-display`.
 * - AMP runtime evaluates the data of subscriptions-display attribute and displays the component
 *  eg:
 *   Data provided by PIANO : {data: {loggedIn: false, p: {showSubscribe: true}}, granted: false, grantReason: "METERING"}
 *   For above given data AMP runtime would display element having `subscriptions-display` attribute as folow:
 *    -> `data.p.showPayWall`
 *    -> `NOT data.loggedIn AND data.p.showPayWall`
 *
 * For more details, Refer following doc:
 * - https://amp.dev/documentation/components/amp-subscriptions/#subscriptions-display
 */
?>
<section subscriptions-section="content-not-granted">
		<div subscriptions-actions subscriptions-display="data.p.showPayWall" id="piano-modal-pay-wall" class="piano-modal">
			<div class="piano-modal-container">
				<div class="piano-modal__top">
		        <div class="piano-modal__content">
		            <div class="piano-modal__header">
		                <div class="piano-modal__image">
		                    <em class="piano-modal__icon-1"></em>
		                </div>
		            </div>
		            <div class="piano-modal__body">
		                <div class="piano-modal__title">
		                    You must subscribe to access this content.
		                </div>
		                <div class="piano-modal__description">
		                    To continue viewing the content you love, please choose one of our subscriptions today.
		                </div>
		            </div>
		        </div>
		    </div>
			    <div class="piano-modal__footer text-align-center">
			        <div class="piano-button piano-modal__button" subscriptions-display="true" subscriptions-action="subscribe" subscriptions-service="local" role="button"
			             tabindex="0">
			            subscribe
			        </div>
			        <div class="piano-modal__signin">
			            Already a subscriber?
			            <div subscriptions-display="true" subscriptions-action="login" subscriptions-service="local" role="button" tabindex="0"
			                 class="piano-link">Sign in here.
			            </div>
			        </div>
			    </div>
			</div>
		</div>
		<div subscriptions-actions subscriptions-display="data.p.showRegWall" id="piano-modal-reg-wall" class="piano-modal">
		    <div class="piano-modal__top">
		        <div class="piano-modal__content">
		            <div class="piano-modal__header">
		                <div class="piano-modal__image">
		                    <em class="piano-modal__icon-1"></em>
		                </div>
		            </div>
		            <div class="piano-modal__body">
		                <div class="piano-modal__title">
		                    Please register to access this content.
		                </div>
		                <div class="piano-modal__description">
		                    To continue viewing the content you love, please sign in or create a new account
		                </div>
		            </div>
		        </div>
		    </div>

		    <div class="piano-modal__footer">
		        <div class="piano-button piano-modal__button piano-modal__button_last" subscriptions-display="true" subscriptions-action="subscribe" subscriptions-service="local" role="button"
		             tabindex="0">
		            register
		        </div>
		        <div class="piano-modal__signin">
		            Already a subscriber?
		            <div subscriptions-display="true" subscriptions-action="login" subscriptions-service="local" role="button" tabindex="0"
		                 class="piano-link">Sign in here.
		            </div>
		        </div>
		    </div>
		</div>
		<div subscriptions-actions subscriptions-display="data.p.showPageviewExpired" id="piano-modal-pageview-expired"
		     class="piano-modal">
		    <div class="piano-modal__top">
		        <div class="piano-modal__content">
		            <div class="piano-modal__header">
		                <div class="piano-modal__image">
		                    <em class="piano-modal__icon-1"></em>
		                </div>
		            </div>
		            <div class="piano-modal__body">
		                <div class="piano-modal__title">
		                    You have used all of your free pageviews.
		                </div>
		                <div class="piano-modal__description">
		                    Please subscribe to access more content.
		                </div>
		            </div>
		        </div>
		    </div>
		    <div class="piano-modal__footer text-align-center">
		        <div class="piano-button piano-modal__button" subscriptions-display="true" subscriptions-action="subscribe" subscriptions-service="local" role="button"
		             tabindex="0">
		            subscribe
		        </div>
		        <div class="piano-modal__signin">
		            Already a subscriber?
		            <div subscriptions-display="true" subscriptions-action="login" subscriptions-service="local" role="button" tabindex="0"
		                 class="piano-link">Sign in here.
		            </div>
		        </div>
		    </div>
		</div>
		<div subscriptions-actions subscriptions-display="NOT data.p.showPayWall AND NOT data.p.showRegWall AND NOT data.p.showPageviewExpired" class="piano-modal">
		    <div class="piano-modal__top">
		        <div class="piano-modal__content">
		            <div style="height: 250px; width: 100%; display:flex; justify-content: center;align-items: center;">
		                <div class="piano-modal__icon-1"></div>
		            </div>

		            <div class="piano-modal__body">
		                <div class="piano-modal__title">
		                    <div><strong>Access denied!</strong></div>
		                </div>
		            </div>
		        </div>
		    </div>
		</div>
</section>
