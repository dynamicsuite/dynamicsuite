/**
 * This file is part of the Dynamic Suite framework.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package DynamicSuite
 * @author Grant Martin <commgdog@gmail.com>
 * @copyright 2021 Dynamic Suite Team
 */

/**
 * Command usage.
 */
function printUsage() {
    console.log('Usage:');
    console.log('node build.js');
    console.log('    --prefix {name}')
    console.log('    --js_include_dir {dir}');
    console.log('    --js_output {path}');
    console.log('    --sass_include_dir {dir}');
    console.log('    --css_output {path}');
    console.log('    --component_dir {dir}');
    console.log('    --mixin_dir {dir}');
}

/**
 * Setup script options.
 */
const options = {
    prefix: null,
    js_include_dir: null,
    js_output: null,
    sass_include_dir: null,
    css_output: null,
    component_dir: null,
    mixin_dir: null
};

/**
 * Parse options.
 */
for (const option in options) {
    if (process.argv.indexOf(`--${option}`) !== -1) {
        const value_key = process.argv.indexOf(`--${option}`) + 1;
        if (typeof process.argv[value_key] === 'undefined') {
            printUsage();
            process.exit(1);
        }
        if (process.argv[value_key].startsWith('--')) {
            printUsage();
            process.exit(1);
        }
        options[option] = process.argv[value_key];
    }
}
if (!options.js_output || !options.css_output || !options.prefix) {
    printUsage();
    process.exit(1);
}

/**
 * Set up core requirements.
 */
const fs = require('fs');
const path = require('path');
const os = require('os');
const {execSync} = require('child_process');
const node_prefix = execSync('npm config get prefix').toString().trim();
const html_compiler = require(path.join(node_prefix, 'node_modules/html-minifier'));
const js_compiler = require(path.join(node_prefix, 'node_modules/uglify-es'));
const sass_compiler = require(path.join(node_prefix, 'node_modules/sass'));

/**
 * File listings.
 */
const readFilesRecursively = function(dir, files) {
    let dir_files = fs.readdirSync(dir)
    files = files || []
    for (const file of dir_files) {
        if (file.startsWith('_')) {
            continue;
        }
        if (fs.statSync(dir + "/" + file).isDirectory()) {
            files = readFilesRecursively(dir + path.sep + file, files)
        } else {
            files.push(path.join(dir, path.sep, file))
        }
    }
    return files
}

/**
 * JS to output.
 */
let output_js = '';

/**
 * CSS to output.
 */
let output_css = '';

/**
 * Add included Vue mixins.
 */
if (options.mixin_dir) {
    for (const file of readFilesRecursively(options.mixin_dir)) {
        if (path.extname(file) !== '.js') {
            continue;
        }
        // noinspection JSUnresolvedFunction
        let ugly_js = js_compiler.minify(fs.readFileSync(file).toString());
        if (typeof ugly_js.error !== 'undefined') {
            console.log(ugly_js.error);
            process.exit(1);
        }
        let name = options.prefix + file.replace(options.mixin_dir, '')
          .replace(new RegExp('\\' + path.sep, 'g'), '_')
          .replace(/([A-Z])/g, '_$1')
          .replace(/__/g, '_')
          .toLowerCase();
        name = name.substring(0, name.length - 3)
        output_js += ugly_js.code.replace('export default', `const mixin_${name}=`) + os.EOL;
    }
}

/**
 * Add included Vue components.
 */
if (options.component_dir) {
    for (const file of readFilesRecursively(options.component_dir)) {

        /**
         * Only check Vue files.
         */
        if (path.extname(file) !== '.vue') {
            continue;
        }

        /**
         * Change to file directory for relative paths.
         */
        process.chdir(path.dirname(file));

        /**
         * Read the component.
         */
        let component = fs.readFileSync(file).toString();

        /**
         * Set up the component name.
         */
        let name = options.prefix + file.replace(options.component_dir, '')
          .replace(new RegExp('\\' + path.sep, 'g'), '-')
          .replace(/([A-Z])/g, '-$1')
          .replace(/--/g, '-')
          .toLowerCase();
        name = name.substring(0, name.length - 4)

        /**
         * Return setup.
         */
        let template = '';
        let script = '';
        let style = '';

        /**
         * Get template.
         */
        if (component.includes('<template>') && component.includes('</template>')) {
            template =  component.substring(
              component.indexOf('<template>') + 10,
              component.lastIndexOf('</template>')
            );
            // noinspection JSUnresolvedFunction
            template = html_compiler.minify(template, {
                removeComments: true,
                collapseWhitespace: true,
                keepClosingSlash: true
            });
        }

        /**
         * Get script.
         */
        if (component.includes('<script>') && component.includes('</script>')) {
            script =  component.substring(
              component.indexOf('<script>') + 8,
              component.lastIndexOf('</script>')
            );
            script = script
              .trim()
              .replace('export default {', '');
            let pos = script.lastIndexOf('}');
            script = script.substring(0, pos) + script.substring(pos  + 1)
            script = script.trim();
        }

        /**
         * Assemble the component.
         */
        let component_output = `Vue.component("${name}", {`;
        if (template) {
            component_output += 'template: `' + template + '`'
        }
        if (script) {
            component_output += `,${script}`;
        }
        component_output += '});'
        // noinspection JSUnresolvedFunction
        let minified = js_compiler.minify(component_output);
        if (typeof minified.error !== 'undefined') {
            console.log(minified.error);
            process.exit(1);
        } else {
            output_js += minified.code + os.EOL;
        }

        /**
         * Get style.
         */
        if (component.includes('<style lang="sass">') && component.includes('</style>')) {
            style = component.substring(
              component.indexOf('<style lang="sass">') + 19,
              component.lastIndexOf('</style>')
            );
            // noinspection JSUnresolvedFunction
            output_css += sass_compiler.renderSync({
                data: style,
                indentedSyntax: true,
                outputStyle: 'compressed'
            }).css.toString();
        }

    }
}

/**
 * Any included JS.
 */
if (options.js_include_dir) {
    for (const file of readFilesRecursively(options.js_include_dir)) {
        if (path.extname(file) !== '.js') {
            continue;
        }
        // noinspection JSUnresolvedFunction
        let ugly_js = js_compiler.minify(fs.readFileSync(file).toString());
        if (typeof ugly_js.error !== 'undefined') {
            console.log(ugly_js.error);
            process.exit(1);
        }
        output_js += ugly_js.code;
    }
}

/**
 * Any included SASS.
 */
if (options.sass_include_dir) {
    for (const file of readFilesRecursively(options.sass_include_dir)) {
        if (path.extname(file) !== '.sass') {
            continue;
        }
        process.chdir(path.dirname(file));
        // noinspection JSUnresolvedFunction
        output_css += sass_compiler.renderSync({
            data: fs.readFileSync(file).toString(),
            indentedSyntax: true,
            outputStyle: 'compressed'
        }).css.toString();
    }
}

/**
 * Write JS.
 */
fs.writeFileSync(path.normalize(options.js_output), output_js);

/**
 * Write CSS.
 */
fs.writeFileSync(path.normalize(options.css_output), output_css);