//Pass in a YouTube URL to insert into postgres database
//DEBUG=sequelize* node app.js
const Sequelize = require('sequelize');
//Currently uses keys on stephenhuh@gmail.com account
//https://developers.google.com/identity/protocols/OAuth2ServiceAccount
const google = require('googleapis');
const rp = require ('request-promise');
const fetch = require('node-fetch');
const util = require('util');
const fs = require('fs');
const crypto = require('crypto');
const { spawn } = require('child_process');
const R = require('ramda')
const youtube = google.youtube('v3');
const models = require('./models.js')


function inspectJSON(object) {
  console.log(util.inspect(object, {showHidden: false, depth: null}));
}

/** 
 * Define "global" configurations here 
 * This function attempts to reduce pollution of global scope
 * and instead pass around configurations as needed.
 */
function getConfiguration() {
  return  {
    youtubeApiKey : 'AIzaSyC8LjcCWH62R3lw_P6F__B6W2GLwa9OvyI',
  }
}

/* Sets up Sequelize */
function setupDB() {
  const sequelize = new Sequelize('twine', 'steviejay', '', {
    host: 'localhost',
    dialect: 'postgres',

    //TODO: study why this is...?
    //These configs force connectino to close after 1 second of idle
    //if > 1 connection is max it will hold until 2 connections are given
    pool: {
      max: 1,
      min: 0,
      idle: 1000
    }
  });

  sequelize
    .authenticate()
    .then(() => {
      console.log("Authenticated to Postgres via Sequelize successfully") ;
    })
    .catch((err) => {
      console.log("Unable to connect to Postgres via Sequelize: ", err) ;
    });

  //Use to refresh your database schema
  //sequelize.sync({force: true})

  return sequelize;
}


//https://stackoverflow.com/questions/5717093/check-if-a-javascript-string-is-a-url
//Synchronous
function isUrl(str) {
  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
  '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
  '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
  '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
  '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
  '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
  return pattern.test(str);
};


function writeCaptionsToFS (subs, url)  {
  return new Promise((resolve, reject) => {
    //Gets captions and writes to FS
    const shell = spawn('youtube-dl', ['--skip-download', subs, url]);
    shell.stdout.on('data', (data) => {
      console.log(`stdout : ${data}`);
    });

    shell.stderr.on('data', (err) => {
      reject(err)
    });

    //This may be a race with the process exit
    shell.on('close', (code) => {
      if (code === 0) {
        return resolve(code);
      } else {
        return reject(code);
      }
    });
  })
}

/**
 * Given a Video URL extracts the "source" of the video
 */
function getSource(url) {
  if (url.indexOf("khanacademy") !== -1) return "Khan Academy";
  if (url.indexOf("youtube") !== -1) return "YouTube";
  //TODO: can i throw from the top level? and not attempt to catch it?
  throw new Error("invalid source from url");
}

/* Given a YouTube video URL, handles data saving and related */
async function handleYouTubeVideo(videoUrl, apiKey, Videos) {
  const video_id = videoUrl.match(/watch\?v=(.*)/)[1];
  const data = await getYouTubeInfo(video_id, apiKey);
  writeToDb(data, Videos);
}

/** 
 * Given a video_id uses Videos:list API to 
 * get relevant information about a video.
 */ 
async function getYouTubeInfo(video_id, apiKey) {
  try {
    const result = await fetch(`https://www.googleapis.com/youtube/v3/videos?id=${video_id}&part=snippet%2Cstatistics&key=${apiKey}`);
    const info = await result.json();
    const data = {
      source : info.items[0].snippet.source,
      title : info.items[0].snippet.title,
      url : null, //get URL from somewhere
      captions : null, //get captions from shell script
      goodness : null, //
      tags: info.items[0].snippet.tags,
      description : info.items[0].snippet.description,
      viewCount : info.items[0].statistics.viewCount,
      likeCount : info.items[0].statistics.likeCount,
      dislikeCount : info.items[0].statistics.dislikeCount,
      favoriteCount : info.items[0].statistics.favoriteCount,
      commentCount : info.items[0].statistics.commentCount,
      description : null,
      complexity_of_language : null,
      subdivisions : null
    };
    return data;
  } catch (error) {
    console.warn(error);
  }
}


/*
 * Takes video attributes and writes to 'videos' table in 
 * database.
 */
function writeToDb(data, Videos) {
  //How do we know if this call was successful?
  Videos.create(data);
}


/**
 * The entrypoint of the save app
 * Called main() for simplicty and convention
 */
function main() {
  //First argument is path to node, second is location of script
  const args = process.argv.slice(2);
  const url = args[0];
  const subs = args[1] || "--write-auto-sub";
  const sequelize = setupDB();
  
  const Videos = models(sequelize);
  const config = getConfiguration();

  //Some error checking on what's getting passed in
  if(!isUrl(url)) {
    console.error("You should be passing a valid URL as a command line arg");
    process.exit(-1);
  }

  if (args.length === 2) {
    console.error("Invalid number of args");
    process.exit(-1);
  } 

  //Dispatch table to handle various sources
  const handleSource = {
    'YouTube' : handleYouTubeVideo,
    //'KhanAcademy' : KhanAcademy,
  };

  //Check what type of URL it is.
  const source = getSource(url);

  //Start handling URL
  handleSource[source](url, config.youtubeApiKey, Videos);
}

main();
  
