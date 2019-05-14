const repoAbsolutePath = "C:/www/YetiForceCRM/";

const gulp = require("gulp"),
  cleanCSS = require("gulp-clean-css"),
  autoprefixer = require("gulp-autoprefixer"),
  rename = require("gulp-rename"),
  purify = require("gulp-purifycss"),
  sourcemaps = require("gulp-sourcemaps"),
  purge = require("gulp-css-purge"),
  merge = require("merge-stream");

const stylesPath = repoAbsolutePath + "public_html/layouts/basic/styles/",
  roundcubePath = repoAbsolutePath + "public_html/modules/OSSMail/roundcube/";

gulp.task("clean-css", () => {
  return gulp
    .src(`${stylesPath}Main.css`)
    .pipe(sourcemaps.init())
    .pipe(
      purge({
        trim: false,
        trim_keep_non_standard_inline_comments: false,
        trim_removed_rules_previous_comment: true,
        trim_comments: false,
        trim_whitespace: false,
        trim_breaklines: false,
        trim_last_semicolon: false,
        shorten: true,
        verbose: true
      })
    )
    .pipe(
      purify([
        repoAbsolutePath + "public_html/layout/**/*.js",
        repoAbsolutePath + "layouts/**/*.tpl",
        repoAbsolutePath + "modules/**/*.php"
      ])
    )
    .pipe(sourcemaps.write("/"))
    .pipe(gulp.dest(stylesPath));
});

gulp.task("minify-css", () => {
  return gulp
    .src(`${stylesPath}Main.css`)
    .pipe(
      rename({
        suffix: ".min"
      })
    )
    .pipe(
      autoprefixer(
        "safari 6",
        "ios 7",
        "ie 11",
        "last 2 Chrome versions",
        "last 2 Firefox versions",
        "Explorer >= 11",
        "last 1 Edge versions"
      )
    )
    .pipe(
      purify([
        repoAbsolutePath + "public_html/**/*.js",
        repoAbsolutePath + "layouts/**/*.tpl",
        repoAbsolutePath + "modules/**/*.php",
        repoAbsolutePath + "public_html/**/*.php"
      ])
    )
    .pipe(
      cleanCSS({}, details => {
        console.log(`${details.name}: ${details.stats.originalSize}`);
        console.log(`${details.name}: ${details.stats.minifiedSize}`);
      })
    )
    .pipe(gulp.dest(stylesPath));
});

gulp.task("minify-rc-css", () => {
  let preview = gulp
    .src(roundcubePath + "plugins/yetiforce/preview.css")
    .pipe(
      rename({
        suffix: ".min"
      })
    )
    .pipe(
      autoprefixer(
        "safari 6",
        "ios 7",
        "ie 11",
        "last 2 Chrome versions",
        "last 2 Firefox versions",
        "Explorer >= 11",
        "last 1 Edge versions"
      )
    )
    .pipe(
      cleanCSS({}, details => {
        console.log(`${details.name}: ${details.stats.originalSize}`);
        console.log(`${details.name}: ${details.stats.minifiedSize}`);
      })
    )
    .pipe(gulp.dest(roundcubePath + "plugins/yetiforce/"));

  let skin = gulp
    .src(roundcubePath + "skins/yetiforce/yetiforce.css")
    .pipe(
      rename({
        suffix: ".min"
      })
    )
    .pipe(
      autoprefixer(
        "safari 6",
        "ios 7",
        "ie 11",
        "last 2 Chrome versions",
        "last 2 Firefox versions",
        "Explorer >= 11",
        "last 1 Edge versions"
      )
    )
    .pipe(
      cleanCSS({}, details => {
        console.log(`${details.name}: ${details.stats.originalSize}`);
        console.log(`${details.name}: ${details.stats.minifiedSize}`);
      })
    )
    .pipe(gulp.dest(roundcubePath + "skins/yetiforce/"));

  return merge(preview, skin);
});
