//Currently uses keys on stephenhuh@gmail.com account
//https://developers.google.com/identity/protocols/OAuth2ServiceAccount
const google = require('googleapis');
const rp = require ('request-promise');
const fetch = require('node-fetch');
const util = require('util');
const fs = require('fs');
const youtube = google.youtube('v3');

const inspectJSON = function(object) {
  console.log(util.inspect(object, {showHidden: false, depth: null}));
}

const API_KEY = 'AIzaSyD_vZ3xxGoO-o6juGvHEDatbQyjnx81euc';
const video_id = '8aGhZQkoFbQ'

async function getYouTubeInfo() {
  try {
    const result = await fetch(`https://www.googleapis.com/youtube/v3/videos?id=${video_id}&part=snippet%2Cstatistics&key=${API_KEY}`);
    const info = await result.json();
    console.log(JSON.stringify(info));
    inspectJSON(info);
  } catch (error) {
    console.warn(error);
  }
}

getYouTubeInfo();
