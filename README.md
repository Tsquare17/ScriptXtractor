# Tsquare\ScriptXtractor
A package that allows the use of JavaScript in Blade Templates on WordPress, without inlining.

ScriptXtractor allows you to work with component specific scripts right in your template, without inlining your scripts, by parsing the specified templates path, compiling discovered scripts, and enqueueing them as specified in the template.

### Examples
* Add a script that will be enqueued in the footer on all pages:
```blade
{{-- beginjs --}}
    let foo = 'bar';
{{-- endjs --}}
```

* Add a script that is enqueued only on page example-slug, in the header:
```blade
{{-- beginjs:example-slug:false --}}
    let foo = 'bar';
{{-- endjs --}}
```

### Considerations

At the moment, the major drawback to this method is that we can't exclude scripts from compilation, based on conditions in the template. **ALL** of the scripts in the templates will be compiled.