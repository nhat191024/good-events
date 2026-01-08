# Write me a api routes for a mobile app. Use sanctum for auth.
## the api requirements: We have a few screens that needs to pack all the needed info and not just simple REST api for each tables. Like for example, the home page needs some special props that the app needs to preload. Im going to tell you what are the routes that needs special props, and then other than that, ALL the tables and Models would have the same REST api format like usual.
warning: for spatie media!.... i need to always return the conversion version of the image, so im going to include the part that has this code... 
```
'thumbnail' => $this->getFirstMediaUrl('thumbnail', 'thumb'),
```
for example. you have to include the correct collection, by checking the Model, for example, the 'app/Models/Blog.php' model.
now i will show you my api plan, feel free to add suggestions in the plan file if you do see better practice.
my ideas file:

```ideas-file.md
file is at ideas-file.md in the project root (relative path)
```

## note: all the api goes into the folder: 'routes/api'. And you have to split files based on the context for the best readability. Also all new API related controllers goes to 'app/Http/Controllers/Api'. When you want to return the resource file, first create a 'Api' folder under 'app/Http/Resources' and create them inside that 'app/Http/Resources/Api' folder. Only use GET/POST for our API, dont use put/patch. Also my ideas file above does not specify all the api, it only specify the ones that has special props and params... So aside from doing the API in that file, you need to plan other apis for the rest of the existing tables/models, you have to research across the project files for that. Use MCP server for API testing (playwright), I do include some for you, like laravel-boost, playwright... 
# Also write a how-to-use-api.md file, describing how to use each api route. This should only be doing AFTER the task is done.

now, start the task inside [api_implementation_plan.md](api_implementation_plan.md), mark the task as done [x] when it is done. And mark the task as ongoing [-] for the task you are currently on so we won't lost the progress on power outage.

