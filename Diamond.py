#coding:utf-8
import re

cardNum = 30	
cards = {
	"1" : 1,
	"2" : 1,
	"3" : 1,
	"4" : 1,
	"5" : 2,
	"7" : 2,
	"9" : 1,
	"11" : 2,
	"13" : 1,
	"14" : 1,
	"15" : 1,
	"17" : 1,
	"poison" : 3,
	"bom" : 3,
	"rock" : 3,
	"snake" : 3,
	"scorpion" : 3,
}

class Game(object):
	"""
	ゲームの動作
	ゲームのルールを記述
	"""
	def __init__(self,peopleNum):
		global cards
		self.cards = dict(cards)
		self.peopleNum = peopleNum
		self.outCards = {}
		self.putDiamonds = []
		points = [ (int(point)/self.peopleNum)*cards[point] for point in cards if re.match(r'[0-9]',point)]
		aveAcq = reduce(lambda x,y:x+y,points)/len(points)
		self._AVERAGE = (13*aveAcq)/2
		
	def freq(self, backNum = 0):
		points = [ (int(point)/(self.peopleNum - backNum))*self.cards[point] for point in self.cards if re.match(r'[0-9]',point)]
		aveAcq = reduce(lambda x,y:x+y,points)/len(points)
		trblNum = [2 if not re.match(r'[0-9]',trbl) else 0 for trbl in self.outCards]
		trblFreq = reduce(lambda x,y:x+y,trblNum) / (30.0 - reduce(lambda x,y:x+y,[num for num in self.outCards.values()]))
		print "ave",aveAcq,"trbl",trblFreq
		return [aveAcq,trblFreq]

	def stepNext(self,card):
		self.cards[card] -= 1
		if card in self.outCards.keys():self.outCards[card] += 1
		else:self.outCards[card] = 1
		if (not re.match(r'[0-9]',card))and(self.outCards[card] == 2): getNum = False
		elif re.match(r'[0-9]',card):
			getNum = int(card) / self.peopleNum
			self.putDiamonds.append(int(card) - getNum * self.peopleNum)
		else: getNum = True
		return getNum
	
	def subPeople(self):
		self.peopleNum -= 1
		
	def backAction(self, backNames):
		getNum = 0
		for i in self.putDiamonds:
			getNum += i / len(backNames)
		return getNum
		
class Human(object):
	"""
	人の動作を記述
	"""
	
	def __init__(self, getDia=0, judgeParam=0.5):
		self.diamonds = 0
		self.getDia = getDia
		self.judgeParam = judgeParam

	def addDiamond(self,num):
		self.diamonds += num

	def judge(self,freq):
		if freq[1] >= self.judgeParam : return False
		else : return True 

	def changeParam(self,calcParam):
		self.judgeParam = calcParam


class Me(Human):
	"""
	自分の動作を記述
	Humanクラスを継承
	"""
	def brain(self,othsCls,freq,GameObj,cnt):
		backNum = reduce(lambda x,y:x+y,[1 if othCls.judge(freq) is False else 0 for othCls in othsCls.values()])
		backGetNum = GameObj.backAction([True for i in range(backNum+1)]) #Trueはダミーデータ
		freq = GameObj.freq(backNum)
		goGetNum = freq[0]
		trblFreq = freq[1]
		if trblFreq == 0: breakMe = False
		elif (self.diamonds + backNum) > GameObj._AVERAGE: breakMe = True 
		elif backGetNum >= goGetNum*(9-cnt): breakMe = True
		else: breakMe = False
		return breakMe
	

def main():
	print "Please name of other players :",
	names = raw_input().split(',')
	peopleNum = len(names) + 1
	print
	print "Please number of games :",
	gameNum = int(raw_input())
	print 
	MeObj = Me()
	OthsObj = {name:Human() for name in names}
	#for name in names:
	#	#print "Please number of",name,"diamonds :",
	#	#diamonds = raw_input()
	#	#print
	#	OthsObj.update({name:Human()})
	for num in range(gameNum):
		print cards
		NextObj = {}
		GameObj = Game(peopleNum)
		cnt = 0
		print "#"*20
		print "New Game !!"
		print "#"*20
		breakMe = False
		while len(OthsObj)!=0 or breakMe is False:
			for name in OthsObj:
				print name,OthsObj[name].diamonds
			print "Me",MeObj.diamonds
			
			while True:
				try:
					print "Please out of a card :",
					card = raw_input()
					getNum = GameObj.stepNext(card)
					break
				except KeyError:
					print "KeyyErorr",card
				print
			cnt += 1

			if getNum is False:
				for name in OthsObj: OthsObj[name].diamonds = 0
				break
			if re.match(r'[0-9]',card):
				for OthObj in OthsObj.values():OthObj.addDiamond(getNum)
				if breakMe is False:
					MeObj.addDiamond(getNum)
			freq = GameObj.freq()
			breakMe = MeObj.brain(OthsObj,freq,GameObj,cnt)
			print "*"*10
			print breakMe
			print "*"*10
			while True:
				try:
					print "Please name of back players :",
					tmp = raw_input()
					if len(tmp):
						backNames = tmp.split(",")
						if breakMe: #他の人と一緒に自分も帰るとき
							backNames.append("Me")
						getNum = GameObj.backAction(backNames)
						for backName in backNames:
							if backName == "Me":
								MeObj.addDiamond(getNum)
							else:
								OthsObj[backName].addDiamond(getNum)
								NextObj[backName] = Human( getDia = OthsObj[backName].diamonds + OthsObj[backName].getDia, judgeParam = freq[1] )
								del OthsObj[backName]
							GameObj.subPeople()
					elif breakMe:
						getNum = GameObj.backAction(["Me"])
						MeObj.addDiamond(getNum)
							
					break
				except KeyError:
					print "KeyError :",backName
					continue
		if breakMe is False: MeObj.diamonds = 0
		else : MeObj.getDia += MeObj.diamonds
			
		for name in OthsObj.keys():
			NextObj[name] =  Human( getDia = OthsObj[name].getDia )
		OthsObj = NextObj

if __name__ == "__main__":
	main()
