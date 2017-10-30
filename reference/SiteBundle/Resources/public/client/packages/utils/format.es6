export const formatNumber = (num) => {
  const str = `${num}`;
  const nums = {
    longHand: num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ","),
  };

  if (num < 1000000) {
    nums.shortHand = `${(num / 1000).toFixed(num % 1000 !== 0)}k`;
  } else {
    nums.shortHand = `${(num / 1000000).toFixed(num % 1000000 !== 0)}M`;
  }
  if (num < 10000 && num > 999) {
    nums.shortHand = `${str.charAt(0)},${str.substring(1)}`;
  }
  if (num < 1000) {
    nums.shortHand = str;
  }

  return nums;
};

export default { formatNumber };
