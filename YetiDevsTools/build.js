const repoAbsolutePath = "C:/www/YetiForceCRM/public_html/";
const rollup = require("rollup"),
  babel = require("rollup-plugin-babel"),
  finder = require("findit")(repoAbsolutePath),
  path = require("path"),
  sourcemaps = require("rollup-plugin-sourcemaps");
let filesToMin = [];

async function build(fileName) {
  //rollup input and output options
  const inputOptions = {
      input: fileName,
      plugins: [
        babel({
          babelrc: false,
          presets: [
            [`babel-preset-env`, { modules: false }],
            [
              `babel-preset-minify`,
              {
                typeConstructors: false,
                mangle: false
              }
            ]
          ],
          plugins: [
            `babel-plugin-external-helpers`,
            `babel-plugin-transform-object-rest-spread`,
            `babel-plugin-transform-es2015-classes`
          ]
        }),
        sourcemaps()
      ]
    },
    outputOptions = {
      sourcemap: true,
      file: fileName.replace(".js", ".min.js"),
      format: "cjs"
    };
  // create a bundle
  const bundle = await rollup.rollup(inputOptions);
  // generate code and a sourcemap
  const { code, map } = await bundle.generate(outputOptions);
  // or write the bundle to disk
  await bundle.write(outputOptions);
}

finder.on("directory", (dir, stat, stop) => {
  const base = path.basename(dir);
  if (
    base === "node_modules" ||
    base === "libraries" ||
    base === "vendor" ||
    base === "_private"
  )
    stop();
});

finder.on("file", (file, stat) => {
  const re = new RegExp("(?<!\\.min)\\.js$");
  if (
    file.includes("roundcube") &&
    !(!file.includes("skins") && file.includes("yetiforce"))
  )
    return;
  if (file.match(re)) filesToMin.push(file);
});

finder.on("end", () => {
  filesToMin.forEach(file => {
    //log files to minify
    console.log(file);
    build(file);
  });
});
