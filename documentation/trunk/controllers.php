<?php
/**
 * @page controllers Using Controllers in the ITul Framework
 * 
 * @section controller_basics Controller Basics
 * 
 * A controller is a class file that is named in a way that it can be associated with a URI.
 * For example, if you request: http://domain.com/blog/index/
 * 
 * The framework would attempt to load the controller class Blog_Controller,
 * and call the function Blog_Controller:index().
 * 
 * The "index" function is loaded by default if the second segment of the URI is empty.
 * The request above could be shortened to: http://domain.com/blog/
 * 
 * The controller class file for the example above would be located in public_html/framework/controller/Blog.php
 * 
 * @section multiword_controllers Multiple Word Controllers
 * 
 * Controllers can be made up of multiple words. In a sample request for: http://domain.com/blog_comment/list/
 * The framework would attempt to load the controller class Blog_Comment_Controller,
 * and call the function Blog_Comment_Controller:list().
 * 
 * The controller class file for the example above would be located in public_html/framework/controller/Blog_Comment.php
 * 
 * @section controller_naming_guidelines Naming Guidelines
 * 
 * <ul>
   <li>Each word begins with a capital letter</li>
   <li>Singular</li>
   </ul>
 */