

/*
 * Get a single result based on available options and their probability
 * @param [mixed,...] options available
 * @param [#,...] 		set of posibilities in int. the higher the #, the higher the posibilities
 * 	fair balance example: [1,1]
 * @return mixed
 */
function getSingleRandomWithProbability(aOptions, aSets)
{
	if (aOptions.length <=0) { return null; }
	if (aOptions.length==1) { return aOptions[0]; }
	if (aOptions.length != aSets.length) { return null; }
	
	var aVaries = [];
	var iIndex;
	var iCount = 0;
	var iMin = 1;
	var aRandom;
	var iResult;
	var bFoundLessThanOne = false;
	
	//console.log(aSets);
	//ensure no 0 or negative values to compare
	do
	{
		bFoundLessThanOne = false;
		for(iIndex = 0; iIndex < aSets.length; iIndex++)
		{
			if (aSets[iIndex] <= 0) { bFoundLessThanOne = true; break; }
		}
		
		if (bFoundLessThanOne)
		{
			for(iIndex = 0; iIndex < aSets.length; iIndex++)
			{
				aSets[iIndex]++;
			}
		}
	} while (bFoundLessThanOne)
	
	//console.log(aSets);
	for(iIndex = 0; iIndex < aSets.length; iIndex++)
	{
		iCount += aSets[iIndex];
	}
	
	iMin = 0;
	aRandom = getRandom(1, iMin, iCount, false);
	//console.log("cnt: " + iCount);
	console.log(aRandom);
	console.log(aSets);
	iCount = 0;
	//finding result
	for(iIndex = 0; iIndex < aSets.length; iIndex++)
	{
		iCount += aSets[iIndex];
		if (iCount >= aRandom[0])
		{
			return aOptions[iIndex];
		}
	}
	
	console.log("getSingleRandomWithProbability: Cannot find option: " + aOptions);
	return null;
}

function getSingleRandom(iMin, iMax)
{
	var aRandom = getRandom(1, iMin, iMax, false);
	return aRandom[0];
}

/*
 * Generate random arrays
 * @param iCount:int number of result
 * @param iMin:int starting #
 * @param iMax:int max #
 * @param bDuplicate:boolean allow / deny duplicate
 * @return [#,...]
 */
function getRandom(iCount, iMin, iMax, bDuplicate)
{
	console.log("min: " + iMin + ", max: " + iMax);
	var c;
	var aResult = [];
	var iRandom;
	var iFactor = 1.0;
	var iMaxFactor = 100000;
	iCount 			= iCount == null? 1 : iCount;
	iMin				= iMin == null? 0 : iMin;
	iMax				= iMax == null? 0 : iMax;
	bDuplicate	= bDuplicate == null? false : bDuplicate;
	
	//to get non decimal min and max, by using ifactor
	while((iMin > 0.0000 && iMin*iFactor % Math.floor(iMin*iFactor) != 0) || (iMax > 0.00000 && iMax*iFactor % Math.floor(iMax*iFactor) != 0))
	{
		console.log("min: " + iMin + ", min-factor: " + (iMin*iFactor) + "/" + Math.floor(iMin*iFactor) + ",f: " + iFactor);
		console.log("max: " + iMax + ", max-factor: " + (iMax*iFactor) + "/" + Math.floor(iMax*iFactor) + ",f: " + iFactor);
		iFactor *=10;
		if (iFactor > iMaxFactor) { iFactor = iMaxFactor; break; }
	}
	
	console.log("factor: " + iFactor);
	
	iMin *= iFactor;
	iMax *= iFactor;
	
	iMin = Math.floor(iMin);
	iMax = Math.floor(iMax);
	
	if (bDuplicate && iMax > 0 && iMax-iMin+1 < iCount)
	{
		//come-on, its not enough to generate...
		return [];
	}
	
	for(c=1; c <= iCount; c++)
	{
		//iRandom = Math.random() * (iMax - iMin);
		iRandom = Math.floor((iMax-(iMin-1))*Math.random()) + iMin;
		console.log("rand: " + iRandom + " from " + iMin + " to " + iMax);
		//iRandom = Math.round(iRandom);
		
		if (! bDuplicate)
		{
			while (in_array(aResult, iRandom))
			{
				iRandom = Math.floor((iMax-(iMin-1))*Math.random()) + iMin;
				console.log("rand: " + iRandom + " from " + iMin + " to " + iMax);
				//iRandom = Math.round(iRandom);
			}
		}
		
		iRandom /= iFactor;
		aResult.push(iRandom);
	}
	
	return aResult;
}

function in_array(aData, mValue)
{
	for( var a = 0; a < aData.length; a++ ) {
    if( aData[a] == mValue ) {
       return true;
    }
		//traceme("data: " + aData[a] + " is not (" + mValue + ")");
 }
 return false;
}
