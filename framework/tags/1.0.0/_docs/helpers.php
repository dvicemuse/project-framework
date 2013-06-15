<?php
/**
 * @page helpers Using Helpers in the ITul Framework

	<h2>Helper Basics</h2>
	<p>
		Helpers are simple classes that, as the name suggests, help you with a task. Sample helper classes
		are Validate, Paginate, Email. Helpers are easily called from
		<a href="controllers.html">Controllers</a>, and <a href="models.html">Models</a>.
	</p>

	<h2>Naming Guidelines</h2>
	<ul>
		<li>Singular</li>
		<li>One word</li>
		<li>Begin with a capital letter</li>
	</ul>



	<h2>Anatomy of a Helper</h2>
	<p>Helper classes are stored in the <var>system/helper/</var> folder.</p>



	<h2>Loading a Helper</h2>
	<p>Helpers can be called from anywhere within the framework construct:</p>
	<code>$this->load_helper('<var>Helper</var>');</code>

	<p>Once loaded, you can access your helper functions using an object with the same name as your class:</p>
	<code>
		$this->load_helper('<var>Paginate</var>');
		<br />$this-><var>Paginate</var>->function();
	</code>

	<p>The load_helper() function returns the helper class object, enabling method chaining. The above example could be rewritten as:</p>
	<code>$this->load_model('Paginate')-><var>function()</var>;</code>


</div>
<!-- END CONTENT -->
 */