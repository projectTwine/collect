const Sequelize = require('sequelize');
module.exports = function models(sequelize) {
  const Videos = sequelize.define('videos', {
    id : {
      type : Sequelize.INTEGER,
      autoIncrement : true,
      primaryKey : true
    },
    video_id {
      type: Sequelize.TEXT,
      unique: true
    },
    channel_id {
      type: Sequelize.TEXT,
    },
    source : {
      type: Sequelize.STRING(50)
    },
    title : {
      type: Sequelize.STRING(300) ,
      unique: true
    },
    url : {
      type: Sequelize.STRING(100) 
    },
    captions : {
      type: Sequelize.TEXT 
    },
    goodness : {
      //how good is this video?
      type: Sequelize.INTEGER 
    },
    tags : {
      //run through a LDA/NLP or alternative algorithm -- what tags  do the captions put out? -- hmm would this be redundant?
      type: Sequelize.ARRAY(Sequelize.TEXT)
    },
    view_count : {
      type: Sequelize.INTEGER 
    },

    like_count : {
      type: Sequelize.INTEGER 
    },
    dislike_count: {
      type: Sequelize.INTEGER 
    },
    favorite_count: {
      type: Sequelize.INTEGER 
    },
    comment_count : {
      type: Sequelize.INTEGER 
    },
    description : {
      type: Sequelize.TEXT 
    },
    complexity_of_language : {
      //would this be redundant? planning to fisch-kincaid
      type: Sequelize.INTEGER
    },
    subdivisions : {
      //I wonder if we can make these foreign keys (edit: cant do that)
      //chunks of videos that talk about a single-topic
      //https://stackoverflow.com/questions/41054507/postgresql-array-of-elements-that-each-are-a-foreign-key
      type: Sequelize.ARRAY(Sequelize.INTEGER)
    }
  });

  return Videos;
}
