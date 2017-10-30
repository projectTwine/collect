export const Client = (config) => {
  if (!config &&
      !config.projectId &&
      !config.writeKey) {
    return false;
  }

  return new window.Keen({
    projectId: config.projectId,
    writeKey: config.writeKey,
    protocol: "auto",
  });
};

export const addOns = () => [
  {
    name: "keen:url_parser",
    input: {
      url: "raw_page_url",
    },
    output: "page_url",
  },
  {
    name: "keen:ip_to_geo",
    input: {
      ip: "ip_address",
    },
    output: "ip_geo_info",
  },
  {
    name: "keen:ua_parser",
    input: {
      ua_string: "raw_user_agent",
    },
    output: "user_agent",
  },
  {
    name: "keen:referrer_parser",
    input: {
      referrer_url: "referrer_url",
      page_url: "raw_page_url",
    },
    output: "referrer_info",
  },
  {
    name: "keen:date_time_parser",
    input: {
      date_time: "keen.timestamp",
    },
    output: "timestamp_info",
  },
];

export default Client;
