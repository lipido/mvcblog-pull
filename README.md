mvcblog-pull
============

A Simple, **pull-based**, Model-View-Controller Blog Example written PHP, for
educational purposes. The MVC framework is embedded in the application itself,
it is not a separate library, so all the code can be easily explored in order to
understand how the pull-based MVC works.

**Note: This is an educational project. If you are looking for a framework for a
professional project, I recommend you to use any other MVC framework (there
are many out there!).**

**Note 2: This is a pull-based MVC framework, which is a less-popular approach.
You can find an action-based MVC framework in [https://github.com/lipido/mvcblog](https://github.com/lipido/mvcblog)**

The main components are implemented in the following way:

1. **Model**. Each domain entity is a class. In addition to domain classes,
there are _data mappers_ which are responsible of SQL sentences needed to
retrieve and save instances of the domain objects from and into the database.
For example Post and PostMapper are domain objects and _data mappers_,
respectively.
2. **View**. Views are plain PHP scripts, with the only responsibility of
generating the data views and user input. Views use one or several *components*.
3. **Components**. Components are PHP classes which
support all the logic needed by views. Components represent the state (rendered
by views), as well as well as behavior by attending user *events* (coming from
views). Regarding their state, components can have different *scopes*.
  1. *Request* scope (default). The *component* is instantiated and destroyed
	in each HTTP request.
  2. *View* scope. The *component* lives while you are in the same *view*, as long as
	the events that are produced inside it do not redirect you to another *view*.
  3. *Session* scope. The *component*'s life is bound to the browser session.


In addition to the minimum components, this example contains:
- A component management facility (`ComponentFactory`), which allows you to
retrieve components taking into account their scope. Normally, components are
requested and used inside views, but you can use components from another
components.
- A view helper class (`ViewManager`), which includes a layout system for the views.
	All your views are embedded inside _layouts_, which contain all the repetivive
	HTML (headers, footers, css declarations, etc). You can use more than one
	layout if you want (in the example, we use two layouts).
- An simple internationalization helper class (`I18n`).

## Event-based
Requests to the server are always directed to a *view*. In addition, some
requests can also include an *event* to be dispatched. An *event* can be seen as
an user action that should be attended by a component. The component dispatches
this event with a method inside the component class and, maybe, could change its
internal state before any *view* is rendered, because *events* are dispatched
**before the view is rendered**.

## View - logic decoupling
A main difference with action-based or push frameworks, this approach decouples
views from controllers (or components). In action based, each view is intended
to render only the output of a single, per-request, action. In the pull-based
model, views can show the state of multiple components, which change their state
accordingly to events. However, attending an event in a request does not mean
that the affected component will be the single-one in the views that will display
the next page to the user.

## URLs structure
Each request is directed to the same entry point, indicating the `view` as a
request parameter, which is always **mandatory**.

```
http://host/index.php?view=myview
```

In the case of an **event** to be processed, two additional parameters are
needed: `component` and `event`.

```
http://host/index.php?view=myview&component=mycomponent&event=mymethod
```

Of course, additional POST and GET parameters can be included in the request in
order to properly dispatch the event (which can be tought as "event
parameters").

## Request lifecycle

Each request performs the following steps:

1. If there is an event to dispatch (component and event parameters in the URL),
obtain the corresponding component and call the corresponding method.
2. The event-dispatching can, optionally, return a view name (`"myviewname"`) to
render. If this is the case, the new view is rendered. The new view can be
reached via redirection if the return value is in the form
`"viewname:redirect"`. Otherwise, the view parameter is used to get the view to
render, so you will remain in the same view. You can also remain in the same
view, but by redirection, which is useful to maintain a POST-REDIRECT-GET
pattern, by simply returning `":redirect"`.
3. Render the view, where the state of all the needed components are queried and rendered.


# Requirements
1. PHP 5.4.0.
2. MySQL (tested in 5.5.40).
3. A PHP-capable HTTP Server (tested in Apache 2).

# Database creation script
Connect to MySQL console and paste this script.
```sql
create database mvcblog;
use mvcblog;
create table users (
		username varchar(255),
		passwd varchar(255),
		primary key (username)
) ENGINE=INNODB DEFAULT CHARACTER SET = utf8;

create table posts (
	id int auto_increment,
	title varchar(255),
	content varchar(255),
	author varchar(255) not null,

	primary key (id),
	foreign key (author) references users(username)
) ENGINE=INNODB DEFAULT CHARACTER SET = utf8;

create table comments (
	id int auto_increment,	 
	content varchar(255),
	author varchar(255) not null,
	post int not null,

	primary key (id),
	foreign key (author) references users(username),
	foreign key (post) references posts(id) on delete cascade
) ENGINE=INNODB DEFAULT CHARACTER SET = utf8;
```
# Create username for the database
Create a username for the database. The connection settings in the PHP code are
in `/core/PDOConnection.php`

```sql
grant all privileges on mvcblog.* to mvcuser@localhost identified by "mvcblogpass";
```

# TODO

- Add a decent CSS.
- Include the URL rewritting mechanism to get pretty urls.
