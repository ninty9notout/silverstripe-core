<section id="stay-in-touch">
	<h2 class="heading"><span>Stay in Touch</span></h2>

	<div class="columns horizontal-gutters">
		<form class="form all-100 tablet-50" action="$SubscriptionPage.Link(Form)" method="post" enctype="application/x-www-form-urlencoded">
			<p><strong>Sign up to our newsletter</strong></p>

			<input name="MailingListSelection[1]" type="hidden" value="1">
			<input name="SecurityID" type="hidden" value="$SecurityID">
			<div class="field">
				<div class="middleColumn append-button">
					<span><input type="email" name="Email" class="text requiredField" placeholder="Email Address" required="required" aria-required="true"></span>
					<button type="submit" name="action_process" value="Join Now" class="button">Join Now</button>
				</div>
			</div>
		</form>

<% cached SiteConfig.LastEdited %>
	<% if Facebook || Twitter || YouTube || GooglePlus %>
		<div class="all-100 tablet-50">
			<p><strong>Join the discussion online</strong></p>

			<ul>
			<% if Facebook %>
				<li><a href="//www.facebook.com/$Facebook"><i class="fa fa-facebook"></i><span> Facebook</span></a></li>
			<% end_if %>
			
			<% if Twitter %>
				<li><a href="//twitter.com/$Twitter"><i class="fa fa-twitter"></i><span> Twitter</span></a></li>
			<% end_if %>
			
			<% if YouTube %>
				<li><a href="//www.youtube.com/$YouTube"><i class="fa fa-youtube"></i><span> YouTube</span></a></li>
			<% end_if %>

			<% if GooglePlus %>
				<li><a href="//plus.google.com/app/basic/$GooglePlus"><i class="fa fa-google-plus"></i><span> Google+</span></a></li>
			<% end_if %>
			</ul>
		</div>
	<% end_if %>
<% end_cached %>
	</div>
</section><!-- #stay-in-touch -->