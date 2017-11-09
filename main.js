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
const { promisify } = require('util');
const R = require('ramda')
const youtube = google.youtube('v3');
const models = require('./models.js');
const removeFile = require('./utils.js').removeFile;

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
function setupDB(sync) {
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
  sequelize.sync({force: sync})

  return sequelize;
}


//https://stackoverflow.com/questions/5717093/check-if-a-javascript-string-is-a-url
//Synchronous
function isUrl(str) {
  if (!str) return false;
  var pattern = new RegExp('^(https?:\\/\\/)?'+ // protocol
  '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.?)+[a-z]{2,}|'+ // domain name
  '((\\d{1,3}\\.){3}\\d{1,3}))'+ // OR ip (v4) address
  '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*'+ // port and path
  '(\\?[;&a-z\\d%_.~+=-]*)?'+ // query string
  '(\\#[-a-z\\d_]*)?$','i'); // fragment locator
  return pattern.test(str);
};


/**
 * Given a Video URL extracts the "source" of the video
 */
function getSource(url) {
  if (url.indexOf("youtube") !== -1) return "YouTube";
  if (url.indexOf("khanacademy") !== -1) return "Khan Academy";
  //TODO: can i throw from the top level? and not attempt to catch it?
  throw new Error("invalid source from url");
}

/**
 * Given a YouTube video URL, handles data saving and related 
 */
async function handleYouTubeVideo(videoUrl, apiKey, Videos) {
  const videoId = videoUrl.match(/watch\?v=(.*)/)[1];
  const data = await getYouTubeInfo(videoUrl, videoId, apiKey);
  console.log("aiite heres what we got for data", data);
  //TODO: do i need this try/catch
  try {
    await writeToDb(data, Videos);
  } catch(error) {
    console.warn(error); 
  }
}

/** 
 * Given a video_id uses Videos:list API to 
 * get relevant information about a video.
 */ 
async function getYouTubeInfo(videoUrl, videoId, apiKey) {
  console.log("videoId:", videoId);
  console.log("videoUrl:", videoUrl);
  try {
    const result = await fetch(`https://www.googleapis.com/youtube/v3/videos?id=${videoId}&part=snippet%2Cstatistics&key=${apiKey}`);
    const info = await result.json();
    console.log("info...:",info.items);
    const data = {
      source : 'YouTube',
      video_id : info.items[0].id,
      channel_id : info.items[0].snippet.channelId,
      title : info.items[0].snippet.title,
      url : videoUrl, //get URL from somewhere
      captions : await getCaptions(videoUrl),
      auto_generated_captions : true,
      goodness : null, //
      tags: info.items[0].snippet.tags,
      description : info.items[0].snippet.description,
      view_count : info.items[0].statistics.viewCount,
      like_count : info.items[0].statistics.likeCount,
      dislike_count : info.items[0].statistics.dislikeCount,
      favorite_count : info.items[0].statistics.favoriteCount,
      comment_count : info.items[0].statistics.commentCount,
      complexity_of_language : null,
      subdivisions : null
    };
    console.log("WE GOT Data is... ", data);
    return data;
  } catch (error) {
    console.warn(error);
  }
}

function createRandomString() {
  const str = crypto.randomBytes(20).toString('hex');
  return str;
}


//Could probably compose these (CORRECTION: reduce these onto an object)
function getCaptions(videoUrl) {
  return new Promise((resolve, reject) => {
    //First write captions to a file, then read out those captions, save into DB
    //FileFormat does not include the extension of file name
    const fileFormat = createRandomString();
    const shell = spawn('youtube-dl', ['--skip-download', '--write-sub', '-o', fileFormat, videoUrl]);

    shell.stdout.on('data', (data) => {
      console.log(`stdout : ${data}`) ;
    });

    shell.stderr.on('data', (err) => {
      reject(err); 
    })

    shell.on('close', async (code) => {
      if (code === 0) {
        let subtitles;
        try {
          const fsReadFileP = promisify(fs.readFile);
          subtitles = await fsReadFileP(fileFormat + '.en.vtt', {encoding: 'utf8'});
          await removeFile(fileFormat + '.en.vtt');
        } catch (err) {
          console.error("Something went wrong in reading the subtitle file :", err);
        }
        return resolve(subtitles);
      } else {
        return reject(code);
      }
    });
  });
}


/*
 * Takes video attributes and writes to 'videos' table in 
 * database.
 */
async function writeToDb(data, Videos) {
  //How do we know if this call was successful?
  try {
    await Videos.create(data)
  } catch (err) {
    console.log("something went wrong with writing to the db:", err);
  }
  return;
}


/**
 * The entrypoint of the save app
 * Called main() for simplicity and convention
 */
async function main() {
  //First argument is path to node, second is location of script
  const args = process.argv.slice(2);
  const url = args[0];
  const subs = args[1] || "--write-auto-sub";
  console.log("Your subtitle options are...", subs);
  const sequelize = setupDB(true);
  
  const Videos = models(sequelize);
  const config = getConfiguration();

  //Some error checking on what's getting passed in
  if(!isUrl(url)) {
    console.error("You should be passing a valid URL as a command line arg, url:", url);
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
  await handleSource[source](url, config.youtubeApiKey, Videos);
  process.exit(0);
}

main();
  
