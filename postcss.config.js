const customExtractor = content => {
  const regExp = new RegExp( /[A-z0-9-:\/]+/g );
  const matchedTokens = []
   let match = regExp.exec(content)

   while (match) {
     if (match[0].startsWith('class:')) {
       matchedTokens.push(match[0].substring(6))
     } else {
       matchedTokens.push(match[0])
     }
     match = regExp.exec(content)
   }
   return matchedTokens;
}

module.exports = {
  plugins: {
    // 'postcss-import': {
    //   from: 'src/global.css',
    // },
    // 'postcss-nested': {},
    'autoprefixer': {
      add: true,
    },
    // 'postcss-reporter': { clearReportedMessages: true },
    ...process.env.NODE_ENV == 'development' ? {} : {
      '@fullhuman/postcss-purgecss': {
        content: [
          './*.php',
          './src/**/*.php',
          //'./lib/**/*.php',
          './page-templates/**/*.php',
          './woocommerce/**/*.php',
          './src/**/*.js',
        ],
        safelist: {
          greedy: [
            /align/,
            /float/,
            /srt/,
            /^bg-/,
            /^is-/,
            /^ra-/,
            /^i-/,
            /!important/
          ],
          deep: [
            /table/,
            /Cart/,
            /woocommerce/,
            /wp-/,
          ],
        },
        extractors: [ {
          extractor: customExtractor,
          extensions: ['php', 'js' ]
        } ]
      },
      'cssnano': {
        preset: [ 'default', {} ],
      }
    },
  }
};