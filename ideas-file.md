note: add apis will have /api/ prefixes. the \<return> or \<pack> or \<input> are the minimum requirements and not absolute requirements, you can add appropriate fields if needed. The \<relate> is used to mark this api route as related or works the same as some function already there on the web version. the \<something> tags will be applied to a route when written next to an api route, and it will be applied to the whole controller. ALL restful api related to get all will have to use pagination, items per pages, and max items per request.

When checking the related or \<relate> controllers/methods, you also need to check if the routes related to it has anything like additional feature of that screen, make that an API, for example, the order page has many features, like add orders, cancel, apply vouchers… when i say OrderController is related to an other OrderController of the web version, that means every other features are also related

client api controller and routes:

- LoginController (/login/google:post, /login:post\<return:role>\<relate:app\Http\Controllers\Auth\AuthenticatedSessionController.php>, /forgot:post)

- RegisterController (/register/partner:post, /register:post)\<relate:app\Http\Controllers\Auth\RegisteredUserController.php>

* HomeController (/event/home:get\<pack:is\_has\_new\_noti,user,current\_money,pending\_orders, confirmed\_orders,pending\_partners, blogs>)\<relate:app\Http\Controllers\Home\HomeController.php>

* AssetController (/asset/home:get\<pack:asset, categories, tags>, /asset/search:get\<filter>)\<relate:app\Http\Controllers\Home\AssetHomeController.php>

* RentalController (/rental/home:get\<pack:asset, categories, tags>, /rental/search:get\<filter>)\<relate:app\Http\Controllers\Home\RentHomeController.php>

* BlogController (/blog/category:get\<note:although in the restful api, there already a category route to get this one, but we make additional route for easier access and relates to the requested blog type>\<input:blog\_type>, /blog/search:get\<filter:note: for the blog type related to `GoodLocationBlogController, `we have advanced filters for this, for more info, check the controller actions>)\<relate:’blog types under app\Http\Controllers\Blog folder, read those files for more info’>

* ProfileController \<relate:`App\Http\Controllers\Settings\ProfileController@edit,App\Http\Controllers\Settings\PasswordController@edit,app\Http\Controllers\Profile(folder)`>

* OrderController \<note: this order controller has many endpoints and features, read routes\client\order-history.php for more info> \<relate:app\Http\Controllers\Client\OrderController.php, `App\Http\Controllers\Client\QuickBookingController@fillOrderInfo`>

* AssetOrderController \<relate:app\Http\Controllers\AssetOrderController.php, routes\client\asset-order-history.php>

* ChatController \<relate:app\Http\Controllers\Client\ChatController.php>

* ClientToPartnerController \<relate:`App\Http\Controllers\Auth\RegisteredUserController@createPartnerFromClient`>

note: also some Details page of each feature are hidden here but also needs to be added as an api too. those details page will have additional props like ‘for\_you’ items or something like ‘related\_products’ like that.

note: the blog has many types of blogs, the params going into the api will cover this by a param like 'blog\_type' so the backend would know which blogtype to get, for more details check the Blog Model.

partner api: 

- DashboardController(/partner/dashboard:get\<pack:user,is\_has\_new\_noti, statistical\_data, popular\_services>)\<relate:’app\Fillament\Partner\Pages\Dashboard.php’>

- ChatController(/partner/chat:get\<relate:app\Filament\Partner\Pages\Chat.php>, /partner/chat/search:get\<relate>)

- PartnerBillController(/partner/real-time-bill:get\<relate:app\Filament\Partner\Pages\RealtimePartnerBill.php>)

- and all other pages under ‘app\Filament\Partner\Pages’

those pages have some minor read/write actions that needs to be included as API too.
